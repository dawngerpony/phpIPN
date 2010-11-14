<p>This page allows you to download prepay ticket data.</p>

<p>Please select a start date for your transaction (in YYYY-MM-DD format, e.g. 2009-02-01) 
    and hit the "Export to CSV" button. You can then save the resulting data to your computer 
    and open it up in an application such as Microsoft Excel to filter and process it as you please.</p>

<?=form_open(site_url("reports/export"))?>
    <?=form_fieldset('Report CSV generation')?>
        <?=form_label('Start date (YYYY-MM-DD)', 'from_date')?>
        <?=form_hidden('export', 'CSV')?>
        <?=form_input('from_date', '')?>
        <?=form_submit('save', 'Export to CSV')?>
    <?=form_fieldset_close()?>
<?=form_close();?>
