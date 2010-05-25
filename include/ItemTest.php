<?php
require_once 'PHPUnit/Framework.php';
require_once 'Item.php';

class ItemTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // @TODO complete
    }
    
    public function testNewItem()
    {
        $type = "test type";
        $name = "test name";
        $number = "test number";
        $quantity = "test quantity";
        $item = new Item($type, $name, $number, $quantity);
        
        $this->assertEquals($name, $item->getName());
        $this->assertEquals($number, $item->getNumber());
        $this->assertEquals($type, $item->getType());
        $this->assertEquals($quantity, $item->getQuantity());
    }
}