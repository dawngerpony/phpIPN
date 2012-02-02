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

require_once 'include/NotifyController.php';
 
class NotifyControllerTest extends PHPUnit_Framework_TestCase {

    private $className = 'NotifyController';
    
    public function setUp() {
        $fixture1 = json_decode(file_get_contents(__DIR__ . "/fixtures/post-capture-1323602513.json"));
    }

    public function testNewObject() {
        $controller = new NotifyController();
        $this->assertNotNull($controller);
    }
    
    public function testCheese() {
        // $this->assertTrue(false);
    }
}

