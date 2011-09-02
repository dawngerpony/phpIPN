<?php
/*
 * testNotify.php
 */
//include_once("../includes.php");

define('DEFAULT_NOTIFY_URL', 'http://prepay.duffj.com/notify.php');

header("Content-Type: text/plain");

error_reporting(E_ALL);

echo "Notify test script\n";
echo "==================\n";

// set up parameters
$fields['cmd'] = '_notify-validate';

$fields['address_country_code'] = 'GB';
$fields['address_name'] = 'Test+User';
$fields['address_status'] = 'confirmed';
$fields['address_street'] = '1+Main+Terrace';
$fields['address_zip'] = 'W12+4LQ';
$fields['address_city'] = 'Wolverhampton';
$fields['address_country'] = 'United+Kingdom';
$fields['address_state'] = 'West+Midlands';

$fields['mc_gross'] = '15.00';
$fields['mc_currency'] = 'GBP';

$fields['item_number1'] = '01';
$fields['tax'] = '0.00';

$fields['payment_date'] = '09%3A12%3A10+Sep+28%2C+2007+PDT';
$fields['payment_status'] = 'Completed';
$fields['payment_type'] = 'instant';

$fields['txn_type'] = 'cart';

$fields['txn_id'] = 'TEST' . time();

$fields['charset'] = 'windows-1252';
$fields['mc_shipping'] = '0.00';
$fields['mc_handling'] = '0.00';

$fields['first_name'] = 'Test';
$fields['last_name'] = 'User';

$fields['mc_fee'] = '0.71';
$fields['notify_version'] = '2.4';
$fields['custom'] = '';
$fields['business'] = 'paypal_1190065579_biz%40dafyddjames.com';
$fields['num_cart_items'] = '1';
$fields['mc_handling1'] = '0.00';
$fields['verify_sign'] = 'AdIsxhauFIpwsCaa9QXKV7cNjGxgASu64XBEBGOrggfc1rgXBrpGmEFt';
$fields['mc_shipping1'] = '0.00';
$fields['item_name1'] = 'Pre+pay+ticket';

$fields['payer_id'] = 'G5KV3TRTXQN6L';
$fields['payer_status'] = 'unverified';
$fields['payer_email'] = 'paypal_1190066309_per%40dafyddjames.com';
$fields['receiver_email'] = 'paypal_1190065579_biz%40dafyddjames.com';
$fields['receiver_id'] = 'XLLRP7LLA86KG';

$fields['payment_fee'] = '';
$fields['quantity1'] = '1';
$fields['mc_gross_1'] = '15.00';
$fields['residence_country'] = 'GB';
$fields['test_ipn'] = '1';
$fields['payment_gross'] = '';

$postargs = '';
foreach($fields as $key => $value)
{
    $postargs .= "&$key=$value";
}
ltrim($postargs, "&");
echo "postargs = [$postargs]\n";

if(true === isset($_GET['url']))
{
    $request = $_GET['url'];
}
else
{
    $request = DEFAULT_NOTIFY_URL;
}

echo "Using request URL $request\n";

// Get the curl session object
$session = curl_init($request);

// Set the POST options.
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// Do the POST and then close the session
$response = curl_exec($session);
curl_close($session);

// Get HTTP Status code from the response
$status_code = array();
preg_match('/\d\d\d/', $response, $status_code) or die("Problem with response");

// Check for errors
switch( $status_code[0] )
{
	case 200:
		break;
	case 503:
		die('Your call failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
		break;
	case 403:
		die('Your call failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
		break;
	case 400:
		// You may want to fall through here and read the specific XML error
		die('Your call failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
		break;
	default:
		die('Your call returned an unexpected HTTP status of:' . $status_code[0]);
}

echo "\nResponse:\n$response\n";

