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

require_once 'include/DBManager.php';
 
class DBManagerTest extends PHPUnit_Framework_TestCase
{
    private $className = 'DBManager';
    
    public function setUp()
    {
        $this->validFieldset = array(
            'payment_date'         => urldecode('09%3A12%3A10+Sep+28%2C+2007+PDT'),
            'payment_status'       => 'Completed',
            'payment_type'         => 'instant',

            'mc_gross'             => "15.00",
            'mc_currency'          => "GBP",
            'mc_fee'               => "mc_fee TEST",

            'first_name'           => "testDbUtil",
            'last_name'            => "User",

            'txn_type'             => "cart",
            'txn_id'               => "2WF26980GL452844A",

            'address_zip'          => 'W12+4LQ',
            'address_country_code' => 'GB',
            'address_name'         => 'Test+User+DbUtil',
            'address_status'       => 'confirmed',
            'address_street'       => '1+Main+Terrace',
            'address_zip'          => 'W12+4LQ',
            'address_city'         => 'Wolverhampton',
            'address_country'      => 'United+Kingdom',
            'address_state'        => 'West+Midlands',

            'payer_id'             => 'G5KV3TRTXQN6L',
            'payer_status'         => 'unverified',
            'payer_email'          => 'paypal_1190066309_per%40dafyddjames.com',
            'receiver_email'       => 'paypal_1190065579_biz%40dafyddjames.com',
            'receiver_id'          => 'XLLRP7LLA86KG',

            'business'             => "business TEST",
            'num_cart_items'       => "1",

            'quantity1'            => "2",
            'item_name1'           => "Test item name",
        );

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
     * Test that DB instance retrieved from Singleton is of the correct class.
     */
    public function testNewDatabase()
    {
        $db = SingletonFactory::getInstance()->getSingleton($this->className);
        $this->assertInstanceOf($this->className, $db);
    }
    
    /**
     * Test addding of new payment with valid fields.
     */
    public function testAddPaymentWithValidFields()
    {
        $db = SingletonFactory::getInstance()->getSingleton($this->className);
        
        $this->validFieldset['txn_id'] = rand();
        
        $txn = new Transaction($this->validFieldset);
        $status = $db->addTransaction($txn);
        $this->assertEquals($status, Constants::STATUS_OK, "Status is not OK!");
  
    }
    
    /**
     * Test the insertion of a duplicate payment.
     */
    public function testAddDuplicatePayment()
    {
        $db = SingletonFactory::getInstance()->getSingleton($this->className);
        $this->assertInstanceOf($this->className, $db);
        
        $this->validFieldset['txn_id'] = rand();
        
        $txn = new Transaction($this->validFieldset);

        $status = $db->addTransaction($txn);
        $this->assertEquals($status, Constants::STATUS_OK, "Status not OK after first insertion!");
        $status = $db->addTransaction($txn);
        $this->assertEquals($status, Constants::STATUS_ERROR, "Status not ERROR after second insertion attempt!");
    }
}

