<?php
//test
error_reporting(E_ALL); // debug
ini_set('display_errors', '1'); //debug
include "vendor/autoload.php"; // grab the composer stuff
include "BeatportApi.class.php"; // include the class
include "config.php"; // test configuration

$parameters = array(
    'consumer' => CONSUMER,
    'secret' => SECRET,
    'login' => LOGIN,
    'password' => PASSWORD
);

$query = array(
    'facets' => 'labelId:26731',
    'url' => 'releases',
    //'sortBy' => 'publishDate%BDESC', // not working, not sure why
    'perPage' => '150'
);

$api      = new BeatportApi($parameters); // initialise
$response = $api->queryApi($query); // run the query
echo "<pre>"; //prettify the output
print_r($response['results']); // do something with response array
echo "</pre>";
?>
