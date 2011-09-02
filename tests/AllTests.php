<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}
 
require_once 'PHPUnit/TextUI/TestRunner.php';
 
//require_once 'Framework/AllTests.php';

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
        $suite->addTestSuite('LoggerTest');
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
