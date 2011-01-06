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
require_once("../include/configuration.php");
//require_once("../include/DBManager.php");
//require_once("../include/SingletonFactory.php");

/**
 * Planet Angel Instant Payment Notification System Administration Panel.
 * Allows a user to download prepay ticket information.
 *
 * @author Dafydd James <dafydd@cantab.net>
 */

/**
 * 
 * generate database reports
 */
class Reports extends Controller {

    const DB_MANAGER_CLASS_NAME = 'DBManager';
    
    private $tableName;
    
    function __construct() {
        parent::Controller();
        $this->config->load('phpIPN');
        $this->load->helper(array('form', 'url'));
        $this->load->helper('html');
        $this->tableName = $this->config->item('ipn_table_name');
    }
    
    /**
     * index() function - called when page loads.
     */
    function index() {
        $data = array('title' => 'Database Reports');
        $this->load->view('header', $data);
        $this->load->view('reportsView', $data);
        $this->load->view('footer', $data);
    }
    
    function export() {
        if($this->input->post('from_date')) {
            $from_date = $this->input->post('from_date');
            $dateString = date("Y-m-d-Hi");
            $filename = "pa_ipn_export_{$dateString}_from_$from_date.csv";
            $csvResponse = $this->getCsvResponse($from_date);
            log_message('debug', print_r($csvResponse, true));
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Length: " . mb_strlen($csvResponse));
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=$filename");
            echo $csvResponse;
        } else {
            echo "ERROR";
        }
    }
    
    /**
     * Query the DB for a specific transaction ID.
     */
    function query() {
        $data = array('title' => 'Database Reports');
        $fields = array('txn_id', 'last_name');

        foreach($fields as $field) {
            if($this->input->post($field)) {
                $value = $this->input->post($field);
                $query = $this->queryDatabase($field, $value);
                $data['query'] = $query;
                //break;
            }            
        }

        $this->load->view('header', $data);
        $this->load->view('reportsView', $data);
        $this->load->view('footer', $data);
    }
    
    function queryDatabase($column, $value) {
        $this->load->database();
        //$cols = "id,add_ts,pa_ticket_id,payment_date,mc_gross,payment_status,first_name,last_name,txn_id,address_name,address_street,address_city,address_state,address_country,address_country_code,address_zip,payer_email,payer_status,payer_id,mc_fee,num_cart_items,quantity1,item_name1,item_number1,quantity2,item_name2,item_number2,quantity3,item_name3,item_number3,quantity4,item_name4,item_number4,quantity5,item_name5,item_number5";
        $cols = "id,add_ts,pa_ticket_id,payment_date,mc_gross,payment_status,first_name,last_name,txn_id";
        $sql_query = "select $cols from {$this->tableName} where $column = '$value'";
        $query = $this->db->query($sql_query);
        $this->load->dbutil();
        $delimiter = ",";
        return $query;
    }

    /**
     * Formulate a CSV response containing the database export data.
     */
    function getCsvResponse($from_date) {
        $this->load->database();
        $table = $this->tableName;
        $sql_query = "select * from $table where add_ts >= '$from_date'";
        $query = $this->db->query($sql_query);
        $this->load->dbutil();
        $delimiter = ",";
        return $this->dbutil->csv_from_result($query, $delimiter);
    }
}