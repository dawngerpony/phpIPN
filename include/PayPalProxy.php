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

/**
 * Proxy for PayPal communication - factored out for maintainability
 * and easier unit testing.
 */
class PayPalProxy {

    protected $ipnPort;

    function __construct($isSandbox) {
        $this->ipnPort = Config::$ipnPort;
        $this->isSandbox = true;
    }
    
    function getPayPalUrl() {
        return $this->isSandbox ? Config::$paypalUrlBeta : Config::$paypalUrl;
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
            Logger::error("HTTP error! Couldn't open socket to $paypalUrl");
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
}
