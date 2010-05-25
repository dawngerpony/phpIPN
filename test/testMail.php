<?php
$rand = rand();
// script to test mail
$from = "test@planetangel.net";
$recipients = 'D.M.T.James.00@cantab.net, dafydd.james.test@gmail.com, dafyddmtjames@yahoo.co.uk';
$subject    = 'TEST ' . $rand;
$mailBody   = 'test body';
$headers    = 'From: ' . $from . "\r\n";
$mailStatus = mail($recipients, $subject, $mailBody, $headers);
echo "Sent mail to $recipients. Subject: $subject. Mail status: $mailStatus\n";
