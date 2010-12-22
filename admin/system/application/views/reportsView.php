<p>This page allows you to download prepay ticket data.</p>

<p>Please select a start date for your transaction (in YYYY-MM-DD format, e.g. 2009-02-01) 
    and hit the "Export to CSV" button. You can then save the resulting data to your computer 
    and open it up in an application such as Microsoft Excel to filter and process it as you please.</p>

<? // e.g. http://localhost/~dafydd/phpIPN/admin/index.php/reports/export ?>
<?=form_open("reports/export")?>
    <?=form_fieldset('Report CSV generation')?>
        <?=form_label('Start date (YYYY-MM-DD)', 'from_date')?>
        <?=form_hidden('export', 'CSV')?>
        <?=form_input('from_date', '')?>
        <?=form_submit('save', 'Export to CSV')?>
    <?=form_fieldset_close()?>
<?=form_close();?>

<p>Alternatively, query the IPN database for a specific transaction ID or last name:</p>

<?=form_open("reports/query")?>
    <?=form_fieldset('Database query')?>
        <?=form_label('Transaction ID', 'txn_id')?>
        <?=form_input('txn_id', '')?>
        <?=form_label('Last name', 'txn_id')?>
        <?=form_input('last_name', '')?>
        <?=form_submit('save', 'Query database')?>
    <?=form_fieldset_close()?>
<?=form_close();?>

<?php
if(false == empty($query)) {
    echo "<h2>Query Results</h2>\n";
    $resultArray = $query->result_array();
    if(false === empty($resultArray)) {
        echo '<table>';
        foreach($query->result_array() as $index => $row) {
            if($index === 0) {
                echo '<thead style="1px solid;">';
                foreach($row as $column => $value) {
                    echo "<th>$column</th>";
                }
                echo "</thead>";
            }
            echo "<tr>";
            foreach($row as $column => $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No query results\n";
    }
}
?>
