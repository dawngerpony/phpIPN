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
include_once 'Log.php';

/**
 * Logger - manages the logging
 */
class Logger {
    private static $logInstance = null;

    private static function init() {
        $filename = Config::$logFile;
        self::$logInstance = &Log::singleton('file', $filename);
    }

    public static function notice($msg) {
        if(null === self::$logInstance) {
            self::init();
        }
        self::$logInstance->notice($msg);
    }

    /**
     * Alias for warning().
     */
    public static function warn($msg) {
        self::warning($msg);
    }
    
    public static function warning($msg) {
        if(null === self::$logInstance) {
            self::init();
        }
        self::$logInstance->warning($msg);
    }
    
    public static function debug($msg) {
        if(null === self::$logInstance) {
            self::init();
        }
        self::$logInstance->debug($msg);
    }

    /**
     * Alias for err().
     */
    public static function error($msg) {
        self::err($msg);
    }

    public static function err($msg) {
        if(null === self::$logInstance) {
            self::init();
        }
        self::$logInstance->err($msg);
    }

    public static function info($msg) {
        if(null === self::$logInstance) {
            self::init();
        }
        self::$logInstance->info($msg);
    }
}

