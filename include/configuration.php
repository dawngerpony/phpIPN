<?php
class Config {    
    const IS_BETA           = false;
    const MAIL_ENABLED      = true;
    const DB_ENABLED        = true;
    
    static $rootDir             = "/home/www/www.planetangel.net/prepay";

    /**********************************
     * Database details               *
     **********************************/

    static $dbHost              = "localhost";
    static $dbUser              = "planetangel";
    static $dbPass              = "749cheese";
    static $dbDatabase          = 'pa';
    static $dbTable             = "prepay_tickets";

    static $admins              = "dafyddmtjames@yahoo.co.uk, dafydd.james.test@gmail.com, party@planetangel.net";

    static $mailFromAddress     = "Planet Angel <party@planetangel.net>";
    static $logFile             = "/home/www/www.planetangel.net/prepay/prepay.log";

    static $receiverEmail       = "shop@planetangel.net";
    static $receiverEmailTest   = "paypal_1190065579_biz@dafyddjames.com";

    static $mailTo              = "test@dafyddjames.com";
    static $mailSubject         = "Test Paypal e-mail";
    static $mailTemplate        = "mailtemplate.txt";

    static $currency            = "GBP";

    static $paypalUrlBeta       = "www.sandbox.paypal.com";
    static $paypalUrl           = "www.paypal.com";

    static $idFmtLetters        = "ABCDEF";
    static $idFmtDigits         = "4";

    static $ticketTypes         = array('PREPAY', 'CHILLED', 'CHILLED-ADULT', 'CHILLED-CHILD', 'CHILLED-FAMILY', 'SPECIAL', 'NYE');

//    static $ticketTypes         = "PREPAY, CHILLED-ADULT, CHILLED-CHILD, CHILLED-FAMILY, SPECIAL, NYE";
}
?>
