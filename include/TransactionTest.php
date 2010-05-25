<?php
require_once 'PHPUnit/Framework.php';
require_once 'includes_phpunit.php';
 
class TransactionTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @TODO Describe function
     */
    public function setUp()
    {
        $this->validFieldset = $this->readFieldSet("valid1.txt");

        $this->_host     = Config::$dbHost;
        $this->_user     = Config::$dbUser;
        $this->_pass     = Config::$dbPass;
        $this->_database = Config::$dbDatabase;
        $this->_table    = Config::$dbTable;

        $dsn = "mysql://{$this->_user}:{$this->_pass}@{$this->_host}/$this->_database";
        $this->_dbh =& MDB2::singleton($dsn);
        if (PEAR::isError($this->_dbh)) {
            die($this->_dbh->getMessage());
        }
    }

    /**
     * @TODO Describe function
     */
    public function testNewTransactionFailWithEmptyArray()
    {
        try {
            $t = new Transaction(array());
        }
        catch (Exception $e) {
            return;
        }
        $this->fail("Exception was not thrown with empty array!");
    }

    /**
     * @TODO Describe function
     */
    public function testNewTxnValidObject()
    {
        try {
            $t = new Transaction($this->validFieldset);
            $this->assertType("Transaction", $t);
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
    }

    /**
     * @TODO Describe function
     */
    public function testGetPaymentDate()
    {
        try {
            $t = new Transaction($this->validFieldset);
            $field = 'payment_date';
            $expected = urldecode($this->validFieldset[$field]);
            $actual = $t->getPaymentDate();
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $this->assertEquals($expected, $actual, "$field should be $expected, is actually $actual");
    }

    /**
     * @TODO Describe function
     */
    public function testGetTransactionId()
    {
        try {
            $t = new Transaction($this->validFieldset);
            $field = 'txn_id';
            $expected = $this->validFieldset[$field];
            $actual = $t->getTransactionId();
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $this->assertEquals($expected, $actual, "$field should be $expected, is actually $actual");
    }

    /**
     * @TODO Describe function
     */
    public function testNumCartItems()
    {
        try {
            $field = 'num_cart_items';
            list($expected, $actual) = $this->getTestResultsForField($this->validFieldset, $field);
            $expected = urldecode($expected);
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $this->assertEquals($expected, $actual, "$field should be $expected, is actually $actual");
    }

    /**
     * @TODO Describe function
     */
    public function testAddressName()
    {
        try {
            $field = 'address_name';
            list($expected, $actual) = $this->getTestResultsForField($this->validFieldset, $field);
            $expected = urldecode($expected);
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $this->assertEquals($expected, $actual, "$field should be $expected, is actually $actual");
    }

    /**
     * @TODO Describe function
     */
    public function testTransactionParentId()
    {
        try {
            $field = 'parent_txn_id';
            $refundFieldset = $this->readFieldSet("valid3-refund.txt");
            list($expected, $actual) = $this->getTestResultsForField($refundFieldset, $field);
            $expected = urldecode($expected);
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $this->assertEquals($expected, $actual, "$field should be $expected, is actually $actual");
    }
    
    /**
     * @TODO Describe function
     */
    public function testMultipleItems()
    {
        $fieldset = $this->readFieldSet("valid2-multipleitems.txt");
        $num_cart_items = $fieldset['num_cart_items'];
        try {
            $t = new Transaction($fieldset);
        }
        catch (Exception $e) {
            $this->failValidFieldSet($e);
        }
        $items = $t->getItems();
        $numItems = count($items);
        $this->assertEquals($num_cart_items, $numItems);
    }

    /**
     * @TODO Describe function
     */
    protected function failValidFieldSet($e)
    {
        $this->fail("Exception was thrown with valid field set on line " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage() . "\n");
    }
    
    /**
     * @TODO Describe function
     */
    protected function getTestResultsForField($fieldset, $fieldname)
    {
        $t = new Transaction($fieldset);
        $tData = $t->getData();
            
        $expected = $fieldset[$fieldname];
        $actual = $tData[$fieldname];
        
        return array($expected, $actual);
    }

    /**
     * @TODO Describe function
     */
    protected function readFieldSet($filename)
    {
        $fullFilename = "fixtures/$filename";
        $fileArray = file($fullFilename, FILE_IGNORE_NEW_LINES);

        $fieldSet = array();
        foreach($fileArray as $line) {
            $lineArray = explode("=",$line);
            $fieldSet[trim($lineArray[0])] = trim($lineArray[1]);
        }
        return $fieldSet;
    }
        
}

