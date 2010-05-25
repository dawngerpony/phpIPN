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

require_once("MDB2.php");

class DBManager {
    private $_dbh = null; // database handle

    function __construct($params)
    {
        if(!isset($this->_dbh) || empty($this->_dbh)) {
            $this->_host     = Config::$dbHost;
            $this->_user     = Config::$dbUser;
            $this->_pass     = Config::$dbPass;
            $this->_database = Config::$dbDatabase;
            $this->_table    = Config::$dbTable;

            $dsn = "mysql://{$this->_user}:{$this->_pass}@{$this->_host}/$this->_database";
            Logger::debug("Initializing database: mysql://{$this->_user}@{$this->_host}/$this->_database");
            $db =& MDB2::singleton($dsn);
            if (PEAR::isError($db)) {
                die($db->getMessage() . ', ' . $db->getDebugInfo());
            }
            $this->_dbh = $db;
        }
    }

	/*
	 * addTransaction() - add transaction to database, given a list of fields.
	 *                    Only add fields from the list of valid fields.
	 */
    public function addTransaction($txn)
    {
        $txnData = $txn->getData();
        
        $dupFound = $this->checkDuplicateRow('txn_id',$txn->getTransactionId());
        if($dupFound == Constants::DUPLICATE_FOUND) {
            Logger::err("Duplicate transaction ID: " . $txn->getTransactionId());
            return Constants::STATUS_ERROR;
        }
        
        $placeholders = $this->getPlaceholders($txnData);
        $columns = implode(",",array_keys($txnData));
        $values = array_values($txnData);
        
        $query = "INSERT INTO $this->_table ($columns) VALUES ($placeholders)";
        
        $result = $this->executeQuery($query, $values);
    }
    
    /**
     * @TODO Comment this function.
     */
    protected function getPlaceholders($data)
    {
        foreach($data as $field) {
            $placeholders[] = "?";
        }
        return implode(",",$placeholders);
    }

    /**
     * Prepared statement SQL query function.
     * Expects $sql to have 0 or more placeholders ('?') 
     * which will be replaced by values given in the $value_array. Order is important.
     * @return a list of arrays (see above). Where each array represents a database row in an associative array.
     */
    function executeQuery($sql, $value_array = array())
    {
        Logger::debug("SQL: $sql");
        Logger::debug("Values: " . implode(",",$value_array));
        $db = $this->_dbh;
        
        if(!$db) {
            throw new Exception("Database not initialized!");
        }
        
        if(sizeof($value_array) > 0) {
            $value_array = array_values($value_array);
            $sth = $db->prepare($sql);
            if(PEAR::isError($sth)) {
                $msg = "Prepare failed: " . $sth->getMessage() . ', ' . $sth->getDebugInfo();
                Logger::err($msg);
                throw new Exception($msg);
            }
            $result = $sth->execute($value_array);
        } else {
            $result = $db->query($sql);
        }

        if(PEAR::isError($result)) {
            $msg = "Query failed: " . $result->getMessage() . ', ' . $result->getDebugInfo();
            Logger::err($msg);
            throw new Exception($msg);
        }
        
        return $result->fetchAll();
    }

    /*
     * Checks for existing values in the table.
     * Returns DUPLICATE_FOUND if a duplicate was found, 
     * else returns NO_DUPLICATE
     */
    public function checkDuplicateRow($col, $val)
    {
        $query = "SELECT $col from {$this->_table} WHERE $col = ?";
        $result = $this->executeQuery($query, array($val));
        if($result) {
            return Constants::DUPLICATE_FOUND;
        } else {
            return Constants::NO_DUPLICATE;
        }
    }

}
