<h1>Template edit</h1>
<?=form_open(site_url("templates/save/$filename"))?>
    <?=form_fieldset('Template Edit Form')?>
        <div class="textfield">
           <?=form_label('Template Contents', 'template_contents')?>
           <?=form_textarea($textareaParameters)?>
        </div>
        <div class="buttons">
            <?=form_submit('save', 'Save Changes')?>
        </div>
    <?=form_fieldset_close()?>
<?=form_close();?>

<!--<?=$templateContents?>-->