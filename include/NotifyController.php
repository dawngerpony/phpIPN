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
require_once("include/includes.php");

class NotifyController {
    
    protected $isSandbox = true;
    protected $ticketTypes;
    protected $ipnPort;
    
    /**
     * Retrieve relevant values from configuration.
     */
    function __construct() {
        $this->ticketTypes = Config::$ticketTypes;
        $this->ipnPort = Config::$ipnPort;
        $this->additionalRecipient = Config::$additionalRecipient;
        $this->dbEnabled = Config::DB_ENABLED;
        $this->mailEnabled = Config::MAIL_ENABLED;
    }
    
    function getPayPalUrl() {
        return $this->isSandbox ? Config::$paypalUrlBeta : Config::$paypalUrl;
    }
    
    function getReceiverEmail() {
        return $this->isSandbox ? Config::$receiverEmailTest : Config::$receiverEmail;
    }
    
    /**
     * Verify a new POST transaction with PayPal, then process it.
     */
    function run() {
        Logger::notice("Processing new payment notification...");
        Logger::debug("POST data: " . json_encode($_POST));
        $this->isSandbox = (true == isset($_POST['test_ipn']) && ($_POST['test_ipn'] == 1));
        if(true === $this->verifyTransaction($_POST)) {
            Logger::info("Transaction verified!");
            $this->processTransaction($_POST);            
        } else {
            Logger::error("Could not verify transaction with PayPal!");
        }
        Logger::notice("Transaction processing complete!");
    }

    /**
     * Verify an incoming request with PayPal to check that it's real.
     */
    function verifyTransaction($data) {
        Logger::notice("Verifying transaction with PayPal...");
        $isVerified = false;

        $paypalUrl = $this->getPaypalUrl($data);
        Logger::debug("isSandbox = $this->isSandbox, paypal URL = $paypalUrl");

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        // build the request
        foreach ($data as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        // post back to PayPal system to validate
        $header = "";
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

        $fp = fsockopen($paypalUrl, $this->ipnPort, $errno, $errstr, 30);

        if(!$fp) {
            Logger::error("HTTP error! Couldn't open socket to $paypalUrl for txn_id [$txn_id]");
            return false;
        }
        fputs ($fp, $header . $req);
        while (false === feof($fp)) {
            $res = fgets($fp, 1024);
            if(0 == strcmp($res, "VERIFIED")) {
                Logger::info("Transaction verified! res = $res");
                $isVerified = true;
            } elseif(0 == strcmp ($res, "INVALID")) {
                Logger::error("Invalid transaction received (response from Paypal: $res)!");
                // TODO: log for manual investigation
                // TODO: send e-mail to P&A
            }
        }
        fclose ($fp);
        return $isVerified;
    }
    
    /**
     * Checks the database for an existing transaction ID.
     * @return true if it's duplicate, false if not.
     */
    function isDuplicateTxnId($txn_id) {
        $db = SingletonFactory::getInstance()->getSingleton(DB_MANAGER_CLASS_NAME);
        $dupFound = $db->checkDuplicateRow('txn_id', $txn_id);
        if($dupFound == Constants::DUPLICATE_FOUND) {
            Logger::error("Duplicate transaction id: $txn_id!");
            return true;
        } else {
            return false;
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
