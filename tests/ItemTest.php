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
require_once '../include/Item.php';

class ItemTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // @TODO complete
    }
    
    public function testNewItem() {
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