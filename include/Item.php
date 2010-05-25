<?php
/**
 * Item class
 */
class Item
{
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