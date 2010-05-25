<?php
/**
 * Configuration file for Planet Angel prepay system.
 */
class Config
{    
    const IS_BETA           = true;
    const MAIL_ENABLED      = true;
    const DB_ENABLED        = true;
    
    static $rootDir             = "/home/www/beta.planetangel.net/prepay/";

    /**********************************
     * Database details               *
     **********************************/

    static $dbHost                      = "localhost";
    static $dbUser                      = "pangel";
    static $dbPass                      = "passw0rd";
    static $dbDatabase                  = 'pangel';
    static $dbTable                     = "prepay_tickets";

    static $mailFromAddress             = "Planet Angel <party@planetangel.net>";
    static $admins                      = "dafydd@cantab.net,dafyddmtjames@yahoo.co.uk";
    static $logFile                     = "/home/www/beta.planetangel.net/logs/prepay.log";

    static $receiverEmail               = "shop@planetangel.net";
    static $receiverEmailTest           = "paypal_1190065579_biz@dafyddjames.com";

    static $mailTo                      = "test@dafyddjames.com";
    static $mailSubject                 = "Test Paypal e-mail";
    static $mailTemplateConfirmation    = "templates/mailtemplate_confirmation.txt";

    static $currency                    = "GBP";

    static $paypalUrlBeta               = "www.sandbox.paypal.com";
    static $paypalUrl                   = "www.paypal.com";

    static $idFmtLetters                = "ABCDEF";
    static $idFmtDigits                 = "4";

    static $ticketTypes                 = array('PREPAY', 'CHILLED', 'CHILLED-ADULT', 'CHILLED-CHILD', 'CHILLED-FAMILY', 'SPECIAL', 'NYE');
}

