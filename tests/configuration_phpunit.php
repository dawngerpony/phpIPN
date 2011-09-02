<?php
class Config {    
    const IS_BETA           = true;
    const MAIL_ENABLED      = true;
    const DB_ENABLED        = true;

    /* Root directory for looking up templates. */
    static $rootDir             = "/home/www/beta.planetangel.net/prepay/";

    /**********************************
     * Database details               *
     **********************************/

    static $dbHost                      = "localhost";
    static $dbUser                      = "pangel";
    static $dbPass                      = "passw0rd";
    static $dbDatabase                  = 'pangel';
    static $dbTable                     = "prepay_tickets";

    /* Comma-separated list of administrative e-mail addresses. */
    static $admins              = "";

    /* Additional recipient for confirmation e-mails. */
    static $additionalRecipient = "";

    /* Address to appear in the "From" portion of the e-mails, e.g. "Guardian Angel <admin@guardianangel.com>" */
    static $mailFromAddress             = "test-phpunit@donotreply.com";
    /* Absolute path to log file, e.g. "/home/www/www.guardianangel.com/prepay/prepay.log" */
    static $logFile                     = "/tmp/prepay.log";

    /* Paypal receiver e-mail - this is the same as the one that goes into the HTML form, e.g. 'admin@guardianangel.com'. */
    static $receiverEmail               = "shop@planetangel.net";
    static $receiverEmailTest           = "paypal_1190065579_biz@dafyddjames.com";

    static $mailTo                      = "test@dafyddjames.com";
    static $mailSubject                 = "Test Paypal e-mail";
    static $mailTemplateConfirmation    = "templates/mailtemplate_confirmation.txt";

    /* Paypal currency - only change this if you're not working in GBP (£ pounds sterling). */
    static $currency                    = "GBP";

    /* Paypal URLs - you shouldn't need to change these! */
    static $paypalUrlBeta               = "www.sandbox.paypal.com";
    static $paypalUrl                   = "www.paypal.com";

    static $idFmtLetters                = "ABCDEF";
    static $idFmtDigits                 = "4";

    static $ticketTypes                 = array('PREPAY', 
                                                'CHILLED', 
                                                'CHILLED-ADULT', 
                                                'CHILLED-CHILD', 
                                                'CHILLED-FAMILY', 
                                                'SPECIAL', 
                                                'NYE', 
                                                'NYECREW');

}

