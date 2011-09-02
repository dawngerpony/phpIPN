<?php
require_once("include/includes.php");
header("Content-type: text/plain\n\n");
echo "Initializing logger... ";
$logger = &Log::singleton('file', Config::$logFile);
echo "done!\n";
echo "Echoing statement: ";
$rand = mt_rand();
$statement = "\"TEST: $rand\"";
$logger->debug($statement);
echo "$statement\n";
$file = file(Config::$logFile);
$pattern = "/$rand/";
$matches = preg_grep($pattern, $file);
if(1 !== count($matches))
{
    echo "ERROR! Couldn't find $rand in log file\n";
}
else
{
    echo "Success! Found $rand in log file\n";
}

