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
 *                                                                      *
 * @author Dafydd James <mail@dafyddjames.com>                          *
 *                                                                      *
 ************************************************************************/
include "Mail.php";

/**
 * @TODO Add comment.
 */
class MailManager {
    protected $from; // mail sender
    protected $adminUsers; // admin users

    const TOKENS_QUANTITY     = "quantity";
    const TOKENS_FIRST_NAME   = "first_name";
    const TOKENS_LAST_NAME    = "last_name";
    const TOKENS_PA_TICKET_ID = "pa_ticket_id";
    const TOKENS_ITEM_NAME    = "item_name";
    const TOKENS_PAYMENT_STATUS = "payment_status";

    /**
     * Retrieve configuration information.
     */
    function __construct($params)
    {
        $this->from = Config::$mailFromAddress;
        $this->adminUsers = Config::$admins;
    }

    /**
     * Sends a mail that a transaction has taken place to the administrator(s).
     */
    public function sendTransactionMailToAdmins($params)
    {
        $this->checkConfirmationMailParams($params);
        
        $firstName = $params[self::TOKENS_FIRST_NAME];
        $lastName = $params[self::TOKENS_LAST_NAME];
        $quantity = $params[self::TOKENS_QUANTITY];
        $item_name = $params[self::TOKENS_ITEM_NAME];
        $payment_status = $params[self::TOKENS_PAYMENT_STATUS];
        $subject = "PREPAY <$payment_status> $quantity tkts $firstName $lastName ($item_name)";
        $template = "admin_notify";
        $this->sendMailFromTemplate($this->adminUsers, $subject, $params, $template);
    }
    
    /**
     * Sends an error mail to the system administrators.
     */
    public function sendErrorMailToAdmins($msg)
    {
        $subject = "Prepay System Error";
        $mailBody = "The prepay system generated the following error: $msg";
        $this->sendPlainTextMail($this->from, $this->adminUsers, $subject, $mailBody);
    }
    
    /**
     * Sends a confirmation mail to the user.
     */
    public function sendConfirmationMailToUser($to, $params, $ticketType)
    {
        $this->checkConfirmationMailParams($params);
        $item_name = $params['item_name'];
        unset($params['item_name']);
        $parts = explode(":", $item_name);
        //Logger::debug("parts: " . print_r($parts,true));
        $partyName = ltrim($parts[1]);
        $subject = "Ticket Confirmation: {$partyName}";
        $template = "confirmation_" . $ticketType;
        Logger::debug("Sending to $to with subject [$subject] using template $template");
        $this->sendMailFromTemplate($to, $subject, $params, $template);
    }
    
    /**
     * @TODO comment this function
     */
    protected function sendMailFromTemplate($recipients, $subject, $params, $templateFilename, $plaintext = true)
    {
        $fullTemplateFilename = dirname(__FILE__) . "/../templates/$templateFilename.txt";
        Logger::debug("Full template filename: $fullTemplateFilename");
        $template = file_get_contents($fullTemplateFilename);

        // @TODO Replace this with proper template stuff
        $mailBody = $this->replaceTokens($template, $params);
        
        if(true === $plaintext)
        {
            $this->sendPlainTextMail($this->from, $recipients, $subject, $mailBody);
            Logger::notice("Sent plaintext mail to [$recipients] with subject \"$subject\"");
        }
        else
        {
            $mailStatus = $this->sendHtmlMail($this->from, $recipients, $subject, $mailBody);
        }
        
    }
    
    /**
     * Send a plain text e-mail using the mail() function.
     */
    protected function sendPlainTextMail($from, $recipients, $subject, $mailBody)
    {
        $headers = "From: " . $from;
        Logger::debug("Starting mail send ('$subject')...");
        $mailStatus = mail($recipients, $subject, $mailBody, $headers);
        Logger::debug("Mail send complete!");
    }
    
    /**
     * TODO comment this function
     */
    protected function checkConfirmationMailParams($params)
    {
        $expectedParams = array(self::TOKENS_QUANTITY,
                                self::TOKENS_FIRST_NAME,
                                self::TOKENS_LAST_NAME,
                                self::TOKENS_PA_TICKET_ID,
                                self::TOKENS_ITEM_NAME);
                                
        foreach($expectedParams as $expectedParam)
        {
            if(!isset($params[$expectedParam]) || empty($params[$expectedParam]))
            {
                throw new Exception("Missing parameter: $expectedParam");
            }
        }
    }
    
    /**
     * Helper method to replace the body of a mail template with the appropriate 
     * values from the PayPal transaction. This method is only public so that
     * it can be unit tested.
     *
     * @see MailManagerTest
     */
    public function replaceTokens($mailBody, $params)
    {
        $delimiter = "%";
        
        //Logger::debug("Params: " . print_r($params, true));
        //Logger::debug("Mail body before replacement: $mailBody");
        
        
        foreach($params as $key => $value)
        {
            $count = 0;
            $pattern = "/{$delimiter}{$key}{$delimiter}/";
            //Logger::debug("Pattern = $pattern");
            $mailBody = preg_replace($pattern, $value, $mailBody, -1, $count);
            if($count == 0)
            {
                Logger::warn("Token not found in mail body: $key");
            }
        }
        //Logger::debug("Mail body after replacement: $mailBody");
        
        return $mailBody;
    }

    /**
     * Sends a HTML mail.
     * @param string $from The contents of the 'From:' header.
     * @param Array $to An array of recipient addresses.
     * @param string $subject The subject of the e-mail.
     * @param string $message The body of the e-mail.
     */
    protected function sendHtmlMail($from, $recipients, $subject, $message)
    {
        Logger::debug("Recipients: $recipients, from: $from");

        $headers = "From: $from\r\n"; //add From: header
        $headers .= "MIME-Version: 1.0\r\n"; //specify MIME version 1.0

        //unique boundary
        $boundary = uniqid("HTML");

        //tell e-mail client this e-mail contains//alternate versions
        $headers .= "Content-Type: multipart/alternative" . "; boundary = $boundary\r\n\r\n";

        //message to people with clients who don't understand MIME
        $headers .= "This is a MIME encoded message.\r\n\r\n";

        //plain text version of message
        $headers .= "--$boundary\r\n" .
            "Content-Type: text/plain; charset=ISO-8859-1\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n";
        $headers .= chunk_split(base64_encode("This is the plain text version!"));

        //HTML version of message
        $headers .= "--$boundary\r\n" .
            "Content-Type: text/html; charset=ISO-8859-1\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n";
        $headers .= chunk_split(base64_encode("This the <b>HTML</b> version!"));

        //send message
        $status = mail($recipients, $subject, $message, $headers);
        if(false === $status)
        {
            throw new Exception("Mail to recipients [$recipients] was not accepted for delivery");
        }
    }
}
