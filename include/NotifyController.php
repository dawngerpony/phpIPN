<?php
/************************************************************************
 * This file is part of phpIPN.                                         *
 *                                                                      *
 * phpIPN is free software: you can redistribute it and/or modify       *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation, either version 3 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * phpIPN is distributed in the hope that it will be useful,            *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with phpIPN.  If not, see <http://www.gnu.org/licenses/>.      *
 ************************************************************************/
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'configuration.php');

class NotifyController {
    
    protected $isSandbox = true;
    protected $ticketTypes;
    
    /**
     * Retrieve relevant values from configuration.
     */
    function __construct() {
        echo __DIR__;
        $this->ticketTypes = Config::$ticketTypes;
        $this->additionalRecipient = Config::$additionalRecipient;
        $this->dbEnabled = Config::DB_ENABLED;
        $this->mailEnabled = Config::MAIL_ENABLED;
    }
    
    /**
     * Returns the correct receiver e-mail address
     * depending on whether we're using the sandbox or not.
     */
    function getReceiverEmail() {
        return $this->isSandbox ? Config::$receiverEmailTest : Config::$receiverEmail;
    }
    
    /**
     * Verify a new POST transaction with PayPal, then process it.
     */
    function run($data) {
        Logger::notice("Processing new payment notification...");
        if(true === empty($data)) {
            Logger::warn("No POST data received, exiting early.");
        } else {
            Logger::debug("POST data: " . json_encode($data));
            $this->isSandbox = (true == isset($data['test_ipn']) && ($data['test_ipn'] == 1));
            $paypal = new PayPalProxy($this->isSandbox);
            if(true === $paypal->verifyTransaction($data)) {
                Logger::info("Transaction verified!");
                $this->processTransaction($data);            
            } else {
                Logger::error("Could not verify transaction with PayPal!");
            }
            Logger::notice("Transaction processing complete!");
        }
    }

    /**
     * Process an incoming transaction.
     */
    function processTransaction($data) {
        
        // check that txn_id has not been previously processed
        if(true === empty($data['txn_id'])) {
            $this->kaput("No transaction ID was found in the request. You need one of them.");
        }
        $txn_id = isset($data['txn_id']) ? $data['txn_id'] : "UNKNOWN";

        // check that receiver_email is your Primary PayPal email
        $receiverEmail = isset($data['receiver_email']) ? $data['receiver_email'] : '';
        $correctEmail = $this->getReceiverEmail();

        if($receiverEmail != $correctEmail) {
            $this->kaput("Receiver_email from IPN: $receiverEmail, should be $correctEmail");
        } else {
            Logger::debug("E-mails are correct");
        }

        Logger::debug("Parameters all valid: txn_id[$txn_id], receiverEmail[$receiverEmail]");

        Logger::debug("Creating new transaction");
        try {
            $txn = new Transaction($data);
        } catch(Exception $e) {
            $this->kaput($e->getMessage());
        }

        // process payment
        if(true === $this->dbEnabled) {
            // get an instance of the database from the singleton factory
            $db = SingletonFactory::getInstance()->getSingleton(DB_MANAGER_CLASS_NAME);

            Logger::debug("Adding transaction [$txn_id] to database...");
            try {
                $dbStatus = $db->addTransaction($txn);
                if(Constants::STATUS_OK == $dbStatus) {
                    Logger::debug("Added transaction [$txn_id] successfully!");
                }
            } catch(Exception $e) {
                $this->kaput("Error adding payment with txn_id=$txn_id to database: " . $e->getMessage());
            }
        } else {
            Logger::warn("DB disabled, not adding transaction");
        }
        if(true === $this->mailEnabled && Constants::STATUS_OK == $dbStatus) {
            $this->sendMails($txn);
        } else {
            Logger::warn("Not sending mail - either mail is disabled or DB write operation was unsuccessful");
        }
    }
    
    /**
     * Send e-mails.
     */
    function sendMails($txn) {
        $templateFound = false;
        $payment_status = $txn->getField('payment_status');
        $payer_email = $txn->getField('payer_email');
        $mail = SingletonFactory::getInstance()->getSingleton('MailManager');
        foreach($this->ticketTypes as $oneType) {
            $params = array();
            $params['pa_ticket_id'] = $txn->getPaTicketId();
            $params['first_name']   = $txn->getField('first_name');
            $params['last_name']    = $txn->getField('last_name');

            $to = "$payer_email";
            $additionalRecipient = $this->additionalRecipient;
            if(false === empty($additionalRecipient)) {
                $to .= ", $additionalRecipient";
            }

            $prepayItem = $txn->getPrepayItem($oneType);
            if(true === is_null($prepayItem)) {
                continue;
            }
            $templateFound = true;

            $params['quantity'] = $prepayItem['quantity'];
            $params['item_name']    = $prepayItem['item_name'];
            if("Completed" === $payment_status) {
                try {
                    $status = $mail->sendConfirmationMailToUser($to, $params, $oneType);
                } catch(Exception $e) {
                    $this->kaput($e->getMessage());
                }
                Logger::notice("Sent confirmation mail (type=$oneType) successfully to $to");
            }
            $params['payment_status'] = $payment_status;
            try {
                $mail->sendTransactionMailToAdmins($params);
            } catch(Exception $e) {
                $this->kaput($e->getMessage());
            }
            Logger::debug("Sent notification mail to admins successfully, payment_status=$payment_status");
        }
        if(false === $templateFound) {
            Logger::error("Unable to find e-mail template");
        }
    }

    /**
     * Die with a helpful error message and send a mail.
     */
    function kaput($msg) {
        Logger::error("KAPUT: $msg");
        $mail = SingletonFactory::getInstance()->getSingleton('MailManager');
        $mail->sendErrorMailToAdmins($msg);
        die("ERROR: $msg");
    }
    
}
