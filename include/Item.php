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
class Item {
    private $allowedTypes = array('party','chilled','special');
    private $type;
    private $name;
    private $number;
    private $quantity;
    private $index;
    
    /**
     * @TODO complete this comment.
     */
    function __construct($index, $name, $number, $quantity)
    {
        
        switch(strtoupper($number))
        {
            case 'PREPAY-PARTY':
                break;
            case 'PREPAY-CHILLED':
                break;
            case 'PREPAY-SPECIAL':
                break;
            default:
                break;
            
        }
        $this->type = $type;
        $this->name = $name;
        $this->number = $number;
        $this->quantity = $quantity;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

}