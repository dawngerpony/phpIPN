<p>This page allows you to view and edit your phpIPN e-mail templates.</p>

<ul>
    <?php foreach($directoryMap as $filename): ?>
        <li><a href="#<?=$filename?>"><?=$filename?></a></li>
    <?php endforeach; ?>
</ul>
<ul id="template-list">
    <?php foreach($templateContents as $filename => $fileContents): ?>
        <li><h2><a name="<?=$filename?>"><?=$filename?></a></h2>
            <div class="control-panel">
                <ul>
                    <li><a href="<?=site_url("templates/edit/$filename")?>">edit</a></li>
                    <li><a href="#">delete</a></li>
                </ul>
            </div>
            <div class="file-contents">
                <span><?=$fileContents?></span>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
