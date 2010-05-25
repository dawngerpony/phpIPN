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

class Config {    
    const IS_BETA           = false;
    const MAIL_ENABLED      = true;
    const DB_ENABLED        = true;
    
    /* Root directory for looking up templates. */
    static $rootDir             = "";

    /**********************************
     * Database details               *
     **********************************/

    static $dbHost              = "";
    static $dbUser              = "";
    static $dbPass              = "";
    static $dbDatabase          = '';
    static $dbTable             = "";

    /* Comma-separated list of administrative e-mail addresses. */
    static $admins              = "";

    /* Address to appear in the "From" portion of the e-mails, e.g. "Guardian Angel <admin@guardianangel.com>" */
    static $mailFromAddress     = "";
    /* Absolute path to log file, e.g. "/home/www/www.guardianangel.com/prepay/prepay.log" */
    static $logFile             = "";

    /* Paypal receiver e-mail - this is the same as the one that goes into the HTML form, e.g. 'admin@guardianangel.com'. */
    static $receiverEmail       = "";
    static $receiverEmailTest   = "paypal_1190065579_biz@dafyddjames.com";

    static $mailTo              = "test@dafyddjames.com";
    static $mailSubject         = "Test Paypal e-mail";
    static $mailTemplate        = "mailtemplate.txt";

    /* Paypal currency - only change this if you're not working in GBP (£ pounds sterling). */
    static $currency            = "GBP";

    /* Paypal URLs - you shouldn't need to change these! */
    static $paypalUrlBeta       = "www.sandbox.paypal.com";
    static $paypalUrl           = "www.paypal.com";

    static $idFmtLetters        = "ABCDEF";
    static $idFmtDigits         = "4";

    static $ticketTypes         = array('PREPAY', 'CHILLED', 'CHILLED-ADULT', 'CHILLED-CHILD', 'CHILLED-FAMILY', 'SPECIAL', 'NYE', 'NYECREW');
}
