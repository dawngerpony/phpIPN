<h1>phpIPN - Database Reports</h1>

<p>This page allows you to download prepay ticket data. If you don't work for Planet Angel, then why are you here?</p>

<p>Please select a start date for your transaction (in YYYY-MM-DD format, e.g. 2009-02-01) and hit the "Export to CSV" button. You can then save the resulting data to your computer and open it up in an application such as Microsoft Excel to filter and process it as you please.</p>

<form method="get" action="index.php">
    <input type="hidden" name="export" value="CSV" />
    <label for="from_date">Start date (YYYY-MM-DD):</label> <input id="from_date" type="text" name="from_date" value="" />
    <input type="submit" value="Export to CSV"> 
</form>
