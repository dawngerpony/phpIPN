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
    
    function __construct() {
        parent::Controller();
        $this->load->helper(array('form', 'url'));
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
     * Formulate a CSV response containing the database export data.
     */
    function getCsvResponse($from_date) {
        $this->load->database();
        $table = 'prepay_tickets';
        $sql_query = "select * from $table where add_ts >= '$from_date'";
        $query = $this->db->query($sql_query);
        $this->load->dbutil();
        $delimiter = ",";
        return $this->dbutil->csv_from_result($query, $delimiter);
    }
}