<?php
/**
 * Planet Angel Instant Payment Notification System Administration Panel.
 * Allows a user to download prepay ticket information.
 *
 * $Id: index.php 180 2009-03-13 08:27:36Z pangel $
 *
 * @author Dafydd James <dafydd@cantab.net>
 */
require_once("../include/includes.php");

define('DB_MANAGER_CLASS_NAME', 'DBManager');

/**
 * Returns CSV contents of table.
 */
function exportMysqlToCsv($table, $sql_query)
{
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
 
    // Gets the data from the database
    $result = mysql_query($sql_query);
    $fields_cnt = mysql_num_fields($result);
 
    $schema_insert = '';
 
    for ($i = 0; $i < $fields_cnt; $i++)
    {
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
    while ($row = mysql_fetch_array($result))
    {
        //Logger::debug("Row: " . print_r($row, true));
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            if ($row[$j] == '0' || $row[$j] != '')
            {
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $row[$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
                    str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $row[$j]) . $csv_enclosed;
                }
            } else
            {
                $schema_insert .= '';
            }
 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
    } // end while
 
    return $out;
}

if(true === isset($_GET['export']) && false === empty($_GET['export']))
{
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
    if(true === empty($csvData))
    {
        echo "An error occurred, your data is either empty or there's something wrong with the system. Please try again or e-mail dafydd@cantab.net";
    }
    else
    {
        //Logger::debug("CSV data: " . print_r($csvData, true));
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Length: " . strlen($csvData));
        header("Content-type: text/csv");
        //header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");

        echo $csvData;
    }
}
else
{
?>

<html>
<head>
    <title>Planet Angel IPN System Administration</title>
    <link href="http://www.planetangel.net/templates/planetangel/css/template_css.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div id="page" style="margin: 0 auto; width: 800px">
    <img src="/templates/planetangel/images/glowlogo.jpg" align="center">
    <h1>Planet Angel IPN - Administration Panel</h1>
    <p>This page allows you to download prepay ticket data. If you don't work for Planet Angel, then why are you here?</p>

    <p>Please select a start date for your transaction (in YYYY-MM-DD format, e.g. 2009-02-01) and hit the "Export to CSV" button. You can
    then save the resulting data to your computer and open it up in an application such as Microsoft Excel to filter and process it as you please.</p>

    <form method="get" action="index.php">
        <input type="hidden" name="export" value="CSV" />
        <label for="from_date">Start date (YYYY-MM-DD):</label> <input id="from_date" type="text" name="from_date" value="" />
        <input type="submit" value="Export to CSV"> 
    </form>
</div>

</body>
</html>
<?php
}
?>
