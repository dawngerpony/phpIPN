<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

$path = dirname(__file__);
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'PHPUnit/TextUI/TestRunner.php';
 
require_once 'DBManagerTest.php';
require_once 'ItemTest.php';
require_once 'LoggerTest.php';
require_once 'MailManagerTest.php';
require_once 'TransactionTest.php';

class AllTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
 
        //$suite->addTest(Framework_AllTests::suite());
        $suite->addTestSuite('DBManagerTest');
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
