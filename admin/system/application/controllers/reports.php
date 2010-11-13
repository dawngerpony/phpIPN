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
//require_once("../../../../../include/includes.php");

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
    
    function Reports() {
        parent::Controller();   
    }
    
    /**
     * index() function - called when page loads.
     */
    function index() {
        if(true === $this->isExportRequest()) {
            $csvResponse = $this->getCsvResponse();
        } else {
            $data = array('title' => 'Database Reports | phpIPN Admin');
            $this->load->view('header', $data);
            $this->load->view('reportsView', $data);
            $this->load->view('footer', $data);
        }
    }

    /** 
     * Check the GET parameters to see if this request is a request for CSV content.
     */
    function isExportRequest() {
        if(true === isset($_GET['export']) && false === empty($_GET['export'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Formulate a CSV response containing the database export data.
     */
    function getCsvResponse() {
        if(true === isset($_GET['export']) && false === empty($_GET['export'])) {
            $host   = Config::$dbHost;
            $user   = Config::$dbUser;
            $pass   = Config::$dbPass;
            $db     = Config::$dbDatabase;
            $table  = Config::$dbTable;
            $from_date = $_GET['from_date'];
            $sql_query = "select * from $table where add_ts >= '$from_date'";

            Logger::debug("Connecting to DB $db table $table user $user host $host");
            Logger::debug("SQL query: $sql_query");
            $link = mysql_connect($host, $user, $pass);
            mysql_select_db($db);

            $db = SingletonFactory::getInstance()->getSingleton(DB_MANAGER_CLASS_NAME);
            $dateString = date("Y-m-d-Hi");
            $filename = "pa_ipn_export_{$dateString}_from_$from_date.csv";
            $csvData = exportMysqlToCsv($table, $sql_query);
            if(true === empty($csvData)) {
                echo "An error occurred, your data is either empty or there's something wrong with the system. Please try again or e-mail dafydd@cantab.net";
            } else {
                //Logger::debug("CSV data: " . print_r($csvData, true));
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Length: " . strlen($csvData));
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=$filename");

                return $csvData;
            }
        }
    }

    /**
     * Returns CSV contents of table.
     */
    function exportMysqlToCsv($table, $sql_query) {
        $csv_terminated = "\n";
        $csv_separator = ",";
        $csv_enclosed = '"';
        $csv_escaped = "\\";

        // Gets the data from the database
        $result = mysql_query($sql_query);
        $fields_cnt = mysql_num_fields($result);

        $schema_insert = '';

        for ($i = 0; $i < $fields_cnt; $i++) {
            $fieldname = mysql_field_name($result, $i);
            $fieldname = stripslashes($fieldname); // strip any slashes
            $fieldname = str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $fieldname); // escape any double quotes
            $l = $csv_enclosed . $fieldname . $csv_enclosed;
            Logger::debug("\$l = $l");
            $schema_insert .= $l;
            $schema_insert .= $csv_separator;
        } // end for

        $out = trim(substr($schema_insert, 0, -1));
        $out .= $csv_terminated;

        // Format the data
        while ($row = mysql_fetch_array($result)) {
            //Logger::debug("Row: " . print_r($row, true));
            $schema_insert = '';
            for ($j = 0; $j < $fields_cnt; $j++) {
                if ($row[$j] == '0' || $row[$j] != '') {
                    if ($csv_enclosed == '') {
                        $schema_insert .= $row[$j];
                    } else {
                        $schema_insert .= $csv_enclosed . 
                        str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $row[$j]) . $csv_enclosed;
                    }
                } else {
                    $schema_insert .= '';
                }
                if($j < $fields_cnt - 1) {
                    $schema_insert .= $csv_separator;
                }
            } // end for

            $out .= $schema_insert;
            $out .= $csv_terminated;
        } // end while
        return $out;
    }
  
}