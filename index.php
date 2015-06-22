<?php
//test
error_reporting(E_ALL);
ini_set('display_errors', '1');
include "BeatportApi.class.php";
include "config.php"; // test configuration

$parameters = array (
	'consumer'=> CONSUMER,
	'secret' => SECRET,
	'login' => LOGIN,
	'password' => PASSWORD,
	'token' => '',
	'tokensecret' => '',
	'callbackurl' => CALLBACKURL
	);

$api = new BeatportApi ($parameters); // initialise
$response = $api->queryApi ($query); // run the query
echo $response; // do something with response

?>