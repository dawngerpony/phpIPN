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

/**
 * @TODO Add comment here.
 */
class Transaction {
    const MAX_ITEMS  = 10;
    
    protected $_logger;

    protected $addressFields  = array("address_name","address_street","address_city","address_state","address_country","address_country_code","address_zip","address_status");
    protected $mcFields       = array("mc_gross","mc_currency","mc_fee");
    protected $nameFields     = array("first_name","last_name");
    protected $payerFields    = array("payer_email","payer_status","payer_id");
    protected $receiverFields = array("receiver_email","receiver_id");
    protected $paymentFields  = array("payment_date", "payment_status", "payment_type");
    protected $txnFields      = array("txn_type","txn_id");
    protected $otherFields    = array("business","num_cart_items");
    protected $itemFields     = array("quantity","item_name", "item_number");
    protected $optionalFields = array("parent_txn_id");

    /**
     * array of items purchased in the transaction. Each item has "quantity", "name" and "number" indices.
     */
    protected $_items;
    
    protected $_pa_ticket_id;
    
    function __construct($inFields)
    {
        $this->_logger = &Log::singleton('file', Config::$logFile);
        $logger = $this->_logger;

        $this->_requiredFields = array_merge($this->mcFields, 
                                             $this->nameFields, 
                                             $this->receiverFields,
                                             $this->paymentFields);
                                             
        $logger = $this->_logger;
        
        foreach($this->_requiredFields as $requiredField) {
            if(!isset($inFields[$requiredField]) || empty($inFields[$requiredField])) {
                $msg = "Missing field: $requiredField";
                Logger::error($msg);
                throw new Exception($msg);
            }
        }

        // payment_date=15%3A33%3A25+Sep+17%2C+2007+PDT, payment_status=Completed, payment_type=instant
        $this->_fields['payment_date']   = $inFields['payment_date'];
        $this->_fields['payment_status'] = $inFields['payment_status'];
        $this->_fields['payment_type']   = $inFields['payment_type'];

        // mc_gross=15.00, mc_shipping=0.00, mc_handling=0.00, mc_shipping1=0.00, 
        // mc_handling1=0.00, mc_gross_1=15.00, mc_currency=GBP, mc_fee=0.71
        $this->_fields['mc_gross']    = $inFields['mc_gross'];
        $this->_fields['mc_currency'] = $inFields['mc_currency'];
        $this->_fields['mc_fee']      = $inFields['mc_fee'];

        // name
        // first_name=Test, last_name=User
        $this->_fields['first_name'] = $inFields['first_name'];
        $this->_fields['last_name']  = $inFields['last_name'];

        /****** Transaction ******/
        // txn_id=80784224A2769664G, txn_type=cart
        if(isset($inFields['txn_type'])) {
            $this->_fields['txn_type'] = $inFields['txn_type'];
        } 

        if(!isset( $inFields['txn_id']) || empty( $inFields['txn_id'])) {
            throw new Exception("Missing transaction ID!");
        }

        $this->_fields['txn_id'] = $inFields['txn_id'];
        $this->_fields['parent_txn_id'] = isset($inFields['parent_txn_id']) ? $inFields['parent_txn_id'] : 'N/A';
        
        foreach($this->addressFields as $field) {
            $this->_fields[$field] = $inFields[$field];
        }

        //payer_status=unverified
        //receiver_id=XLLRP7LLA86KG
        // payer_id=G5KV3TRTXQN6L

        $this->_fields['payer_email']    = isset($inFields['payer_email']) ? $inFields['payer_email'] : '?';
        $this->_fields['payer_status']   = isset($inFields['payer_status']) ? $inFields['payer_status'] : '';
        $this->_fields['payer_id']       = isset($inFields['payer_id']) ? $inFields['payer_id'] : '?' ;

        $this->_fields['receiver_email'] = $inFields['receiver_email'];
        $this->_fields['receiver_id']    = $inFields['receiver_id'];

        if(!isset($inFields['business'])) {
            throw new Exception('Missing business field');
        }

        $this->_fields['business']       = $inFields['business'];

        //item_name1=Pre+pay+ticket, quantity1=1
        $this->_items = $this->processItems($inFields, $this->itemFields);

        /*
        $prepayItem = $this->getPrepayItem();
        if(null === $prepayItem)
        {
            Logger::debug("Items: " . print_r($this->_items, true));
            throw new Exception("No prepay item could be found!");
        }
        */

        $numItems = count($this->_items);
        if($inFields['payment_status'] == "Completed") {

            $num_cart_items = 1;
            if(true === isset($inFields['num_cart_items']))
            {
                $num_cart_items = $inFields['num_cart_items'];
            }

            if($num_cart_items != $numItems) {
                throw new Exception("num_cart_items ($num_cart_items) doesn't match actual number of items ($numItems)");
            }
            $this->_fields['num_cart_items'] = $num_cart_items;
        }

        $this->_fields = array_map("urldecode",$this->_fields);

        $this->_pa_ticket_id = $this->newId(Config::$idFmtLetters, Config::$idFmtDigits);
        
        $customerName = $this->_fields['first_name'] . " " . $this->_fields['last_name'];
        $txnId = $this->_fields['txn_id'];
        $tktId = $this->_pa_ticket_id;
        if($inFields['payment_status'] == "Completed") {
            $numItems = $this->_fields['num_cart_items'];
        } else {
            $numItems = 0;
        }
        
        Logger::info("Generated new ticket ID for transaction $txnId: $tktId, $customerName bought $numItems items");
    }

    /**
     * @TODO Comment this function.
     */
    public function processItems($input, $itemFields)
    {
        $items = array();
        for($i = 1; $i <= self::MAX_ITEMS; $i++)
        {
            // $name = urldecode($input['item_name' . $i]);
            // $number = urldecode($input['item_number' . $i]);
            // $quantity = urldecode($input['quantity' . $i]);
            
            //$items[] = new Item($name, $number, $quantity)
            
            foreach($itemFields as $field)
            {
                $fieldname = $field . $i;
                //Logger::debug("fieldname: $fieldname. input[fieldname]: {$input[$fieldname]}");
                if(isset($input[$fieldname]))
                {
                    $items[$i][$field] = urldecode($input[$fieldname]);
                }
            }
        }
        return $items;
    }

    /**
     * Return an item by its number.
     */
    public function getItemByNumber($number)
    {
        foreach($this->_items as $item)
        {
            if(true === isset($item['item_number']) && $number === $item['item_number'])
            {
                return $item;
            }
        }
        return null;
    }

    /**
     * Return the prepay item. Default = self::TYPE_PREPAY. Checks type for validity.
     *
     * @param $type Type of prepay item to return
     *
     * @return the item
     */
    public function getPrepayItem($type = Constants::TYPE_PREPAY)
    {
        if(false === in_array($type, Config::$ticketTypes))
        {
            throw new Exception("Invalid type specified: " . $type);
        }
        return $this->getItemByNumber($type);
    }

    /**
     * @TODO complete this comment.
     */
    public function newId($allowedCharacters, $len)
    {
        $l = $allowedCharacters[mt_rand(0,strlen($allowedCharacters)-1)];
        $lowerExponent = ($len*2) < 1 ? 0 : ($len*2)-1;
        $lowerLimit = pow(10, $lowerExponent);
        $upperLimit = (pow(10, $len*2)) - 1;

        $num = rand($lowerLimit, $upperLimit);
        $firstNum = substr($num, 0, $len);
        $secondNum = substr($num, $len, $len);
        
        $id = "$l$l-$firstNum-$secondNum";
        return $id;
    }
    
    public function getTransactionId()
    {
        return $this->_fields['txn_id'];
    }
    
    public function getField($key)
    {
        if(isset($this->_fields[$key]))
        {
            return $this->_fields[$key];
        }
        return null;
    }

    /**
     * Return the PA ticket ID;
     */
    public function getPaTicketId()
    {
        if(isset($this->_pa_ticket_id))
        {
            return $this->_pa_ticket_id;
        }
        return null;
    }
    
    public function getPaymentDate()
    {
        return $this->_fields['payment_date'];
    }
    
    public function getData()
    {
        $data = $this->_fields;
        //$this->_logger->debug(print_r($data, true));
        
        $data = array_merge($data, $this->getItemsWithOriginalFieldNames());
        $data['pa_ticket_id'] = $this->_pa_ticket_id;

        return $data;
    }

    public function getItemsWithOriginalFieldNames()
    {
        $data = array();
        foreach($this->_items as $i => $item) {
            foreach($item as $key => $value) {
                $data[$key . $i] = $item[$key];
            }
        }
        return $data;
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getNumCartItems()
    {
        return $this->_fields['num_cart_items'];
    }

}

