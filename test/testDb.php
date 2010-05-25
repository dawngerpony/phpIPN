<?php
include("MDB2.php");
$dsn = "mysql://pangel:749cheese@mysql.positive-internet.com/pangel";
$db =& MDB2::singleton($dsn);
if (PEAR::isError($db)) {
    die($db->getMessage() . ', ' . $db->getDebugInfo());
}
$sql = "SELECT COUNT(*) from prepay_tickets";
$sth = $db->prepare($sql);
if(PEAR::isError($sth)) {
    $msg = "Prepare failed: " . $sth->getMessage() . ', ' . $sth->getDebugInfo();
    echo $msg;
}
$result = $sth->execute();
var_dump($result->fetchAll());
?>

