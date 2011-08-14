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

// include all necessary files
require_once("include/includes.php");

define('DB_MANAGER_CLASS_NAME', 'DBManager');

$ticketTypes = Config::$ticketTypes;

function kaput($msg) {
    Logger::error("KAPUT: $msg");
    $mail = SingletonFactory::getInstance()->getSingleton('MailManager');
    $mail->sendErrorMailToAdmins($msg);
    die("ERROR: $msg");
}

Logger::notice("Processing new payment notification...");

//Logger::debug("POST: " . print_r($_POST, true));
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

// build the request
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
//Logger::debug("req = $req");

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$isSandbox = (isset($_POST['test_ipn']) && ($_POST['test_ipn'] == 1));
$paypalUrl = $isSandbox ? Config::$paypalUrlBeta : Config::$paypalUrl;
$txn_id = isset($_POST['txn_id']) ? $_POST['txn_id'] : "UNKNOWN";

Logger::debug("isSandbox = $isSandbox, paypal URL = $paypalUrl");
$fp = fsockopen($paypalUrl, 80, $errno, $errstr, 30);

if(!$fp) {
    kaput("HTTP error! Couldn't open socket to $paypalUrl for txn_id [$txn_id]");
    // HTTP ERROR
}
fputs ($fp, $header . $req);
while (false === feof($fp)) {
    $res = fgets($fp, 1024);
    if(0 != strcmp($res, "VERIFIED")) {
        // Do nothing
    } else {
        Logger::info("Transaction verified! res = $res");
        
        // check that txn_id has not been previously processed
        if(true === empty($_POST['txn_id'])) {
            kaput("No transaction ID was found in the request. You need one of them.");
        }
        if(true === isDuplicateTxnId($txn_id, $db)) {
            kaput("Duplicate transaction id: $txn_id");
        } else {
            //Logger::debug("Transaction ID $txn_id isn't duplicate");
        }
        
        // check that receiver_email is your Primary PayPal email
        $receiverEmail = isset($_POST['receiver_email']) ? $_POST['receiver_email'] : '';
        $correctEmail = $isSandbox ? Config::$receiverEmailTest : Config::$receiverEmail;

        if($receiverEmail != $correctEmail) {
            kaput("Receiver_email from IPN: $receiverEmail, should be $correctEmail");
        } else {
            //Logger::debug("E-mails are correct");
        }
        
        Logger::debug("Parameters all valid: txn_id[$txn_id], receiverEmail[$receiverEmail]");
        
        // process payment
        if(true === Config::DB_ENABLED) {
            //Logger::debug("Creating new transaction");

            try {
                $txn = new Transaction($_POST);
            } catch(Exception $e) {
                kaput($e->getMessage());
            }

            //Logger::debug("Adding transaction to database");
            // get an instance of the database from the singleton factory
            $db = SingletonFactory::getInstance()->getSingleton(DB_MANAGER_CLASS_NAME);
            try {
                $db->addTransaction($txn);
            } catch(Exception $e) {
                kaput("Error adding payment with txn_id=$txn_id to database: " . $e->getMessage());
            }
            
            $payment_status = $txn->getField('payment_status');
            
            if(true === Config::MAIL_ENABLED) {
                $templateFound = false;
                foreach($ticketTypes as $oneType) {
                    $params = array();
                    // @TODO Update these with dynamic quantities
                    $payer_email = $txn->getField('payer_email');
                    $to = "$payer_email";
                    $additionalRecipient = Config::$additionalRecipient;
                    if(false === empty($additionalRecipient)) {
                        $to .= ", $additionalRecipient";
                    }

                    $params['pa_ticket_id'] = $txn->getPaTicketId();
                    $params['first_name']   = $txn->getField('first_name');
                    $params['last_name']    = $txn->getField('last_name');

                    $prepayItem = $txn->getPrepayItem($oneType);
                    if(true === is_null($prepayItem)) {
                        //Logger::debug("No match for $oneType");
                        continue;
                    }
                    $templateFound = true;

                    $params['quantity'] = $prepayItem['quantity'];
                    $params['item_name']    = $prepayItem['item_name'];
                    $mail = SingletonFactory::getInstance()->getSingleton('MailManager');
                    if("Completed" === $payment_status) {
                        try {
                            $status = $mail->sendConfirmationMailToUser($to, $params, $oneType);
                        } catch(Exception $e) {
                            kaput($e->getMessage());
                        }
                        Logger::notice("Sent confirmation mail (type=$oneType) successfully to $to");
                    }
                    $params['payment_status'] = $payment_status;
                    try {
                        $mail->sendTransactionMailToAdmins($params);
                    } catch(Exception $e) {
                        kaput($e->getMessage());
                    }
                    Logger::debug("Sent notification mail to admins successfully, payment_status=$payment_status");
                }
                if(false === $templateFound) {
                    Logger::error("Unable to find e-mail template");
                }
            } else {
                Logger::debug("Mail disabled, not sending mail");
            }
        } else {
            Logger::debug("DB disabled, not adding transaction");
        }

    }
    if(strcmp ($res, "INVALID") == 0) {
        $txn_id = isset($_POST['txn_id']) ? $_POST['txn_id'] : 'none supplied';
        $msg = "Invalid transaction received (response from Paypal: $res)! Transaction ID: $txn_id";
        Logger::warn($msg);
        echo $msg;
        // log for manual investigation
        // send e-mail to P&A
    }
}
fclose ($fp);

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

