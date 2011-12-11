<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

$path = __DIR__;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'includes_phpunit.php';
 
require_once 'DBManagerTest.php';
require_once 'ItemTest.php';
require_once 'LoggerTest.php';
require_once 'MailManagerTest.php';
require_once 'TransactionTest.php';
require_once 'NotifyControllerTest.php';

class AllTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        //$suite->addTest(Framework_AllTests::suite());
        $suite->addTestSuite('NotifyControllerTest');
        //$suite->addTestSuite('DBManagerTest'); # doesn't work yet
        $suite->addTestSuite('ItemTest');
        $suite->addTestSuite('LoggerTest');
        $suite->addTestSuite('MailManagerTest');
        $suite->addTestSuite('TransactionTest');
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
