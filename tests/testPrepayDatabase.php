<?php
include_once("../include/includes.php");
$db = SingletonFactory::getInstance()->getSingleton('PrepayDatabase');
$result = $db->executeQuery("select count(*) from prepay_tickets");
var_dump($result);

$result = $db->checkDuplicateRow('txn_id','1H686265E6317124J');
var_dump($result);

//$txn = new Transaction();
//$result = $db->addTransaction($txn);
//var_dump($result);
?>
