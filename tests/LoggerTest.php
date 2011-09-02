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
require_once '../include/Logger.php';
require_once("configuration_phpunit.php");

class LoggerTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @TODO Describe function
     */
    public function setUp()
    {
    }

    public function testLoggerInit()
    {
        $rand = mt_rand();
        $logfile = Config::$logFile;
        $logger = &Log::singleton('file', $logfile);
        $logger->debug("Testing logger: $rand");
        $logfile_contents = file($logfile);
        $pattern = "/$rand/";
        $matches = preg_grep($pattern, $logfile_contents);
        $num_matches = count($matches);
        $this->assertSame(1, $num_matches, "Num matches = $num_matches, $logfile_contents = " . print_r($logfile_contents, true));
    }
}

