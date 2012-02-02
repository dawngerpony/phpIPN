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
    <link rel="stylesheet" type="text/css" href="style.css" />
  </head>
  <body>
    <?php echo $my_html; ?>
  </body>
</html>
