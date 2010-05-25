<?php
require_once 'PHPUnit/Framework.php';
require_once 'includes_phpunit.php';
 
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

