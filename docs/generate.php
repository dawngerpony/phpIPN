<?php
include_once "markdown.php";
if(isset($_GET['filename'])) {
  $filename = $_GET['filename'] . ".markdown";
} else {
  $filename = "index.markdown";  
}
$my_text = file_get_contents($filename);
$my_html = Markdown($my_text);
?>
<html>
  <head>
    <title><?php echo $filename; ?></title>
  </head>
  <body>
    <h1><?php echo $filename; ?></h1>
    <?php echo $my_html; ?>
  </body>
</html>
