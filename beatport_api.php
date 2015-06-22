<?php
/**
 * Beatport OAuth API by Federico Giust
 * Based on Beatport OAuth Connect by Tim Brandwijk
 *
 * Needs beatport_callback.php script to verify the credentials:
 *  <?php
 *       $credentials = array();
 *       foreach ($_GET as $key => $value) {
 *           $credentials[$key] = $value;
 *       }
 *       if (!empty($credentials)) print json_encode($credentials);
 *   ?>
 */

/**
* Include config file where we store the corresponding constants.
*/
include 'config.php';

/**
* Include to log the api calls in the db.
*/
include('log_calls.php');

// Set default timezone to beatport timezone
date_default_timezone_set('America/Los_Angeles');

// Beatport URLs. Note the oauth_callback after the request url. This is needed to catch the verifier string:
$req_url = 'https://oauth-api.beatport.com/identity/1/oauth/request-token?oauth_callback='.urlencode(WEBSITE);
$authurl = 'https://oauth-api.beatport.com/identity/1/oauth/authorize';
$auth_submiturl = "https://oauth-api.beatport.com/identity/1/oauth/authorize-submit";
$acc_url = 'https://oauth-api.beatport.com/identity/1/oauth/access-token';

$conskey = CONSUMERKEY; // Beatport Consumer Key
$conssec = SECRETKEY; // Beatport Consumer Secret
$beatport_login = BEATPORTLOGIN; // Beatport Username
$beatport_password = BEATPORTPASSWORD; // Beatport Password

// URL Parameters to make the api call and generate a JSON object
if(isset($_GET['facets'])) {
	$facets=$_GET['facets'];
}
if(isset($_GET['sortBy'])){
	$sortBy=$_GET['sortBy'];
}
if(isset($_GET['perPage'])){
	$perPage=$_GET['perPage'];
}
if(isset($_GET['id'])){
	$id=$_GET['id'];
}
if(isset($_GET['url'])){
	$url=$_GET['url'];
}

$qrystring = '';

if(isset($facets) && strlen($facets) > 0){
	$qrystring .= '?facets=' . urlencode($facets);
}elseif(isset($id) && strlen($id) >0) {
	$qrystring .= '?id=' . urlencode($id);
}else{
	echo 'Parameter missing';
	exit;
}
if(isset($sortBy) && strlen($sortBy) > 0){
	$qrystring .= '&sortBy=' . urlencode($sortBy);
}
if(isset($perPage) && strlen($perPage) > 0){
	$qrystring .= '&perPage=' . urlencode($perPage);
}
if(isset($url) && strlen($url) > 0){
	$path = $url;
}

/**
 * Step 1: Get a Request token
 */
$oauth = new OAuth($conskey,$conssec);
$oauth->enableDebug();
$oauth->setAuthType(OAUTH_AUTH_TYPE_FORM); // switch to POST request
$request_token_info = $oauth->getRequestToken($req_url);

// Display error if there's been a problem fetching the request token.
if(empty($request_token_info)) {
    print "Failed fetching request token, response was: " . $oauth->getLastResponse();
    exit();
}

/**
 * Step 2: Set Request Token to log in
 */
$oauth->setToken($request_token_info['oauth_token'],$request_token_info['oauth_token_secret']);

/**
 * Step 3: Use request token to log in and authenticate for 3-legged auth. The response (via callback URL in $req_url) contains the OAuth token and verifier
 */
ini_set('max_execution_time', 500);
$submit = "Login";
$url = $auth_submiturl;

// Using cURL generate and make the call to the Beatport API
$curl_connection_bp = curl_init();
curl_setopt($curl_connection_bp, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_connection_bp, CURLOPT_URL, $url);
curl_setopt($curl_connection_bp, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($curl_connection_bp, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT6.0; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
curl_setopt($curl_connection_bp, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl_connection_bp, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection_bp, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl_connection_bp, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl_connection_bp, CURLOPT_VERBOSE, false); // when true, this outputs the oauth_token and oauth_verifier value that are posted to the callback URL
curl_setopt($curl_connection_bp, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
curl_setopt($curl_connection_bp, CURLOPT_REFERER, $curl_connection_bp);
curl_setopt($curl_connection_bp, CURLOPT_FAILONERROR, 0);
$post_string = 'oauth_token='.$request_token_info['oauth_token'] . '&username=' . $beatport_login . '&password=' . $beatport_password . '&submit=Login';
curl_setopt($curl_connection_bp, CURLOPT_POST, true);
curl_setopt($curl_connection_bp, CURLOPT_POSTFIELDS, $post_string);
$beatport_response = curl_exec($curl_connection_bp);
$beatport_response = json_decode($beatport_response);

/**
 * Step 4: Use verifier string to request the Access Token
 */
$get_access_token = $oauth->getAccessToken($acc_url, "", $beatport_response->oauth_verifier);
if(empty($get_access_token)) {
    print "Failed fetching access token, response was: " . $oauth->getLastResponse();
    exit();
}

/**
 * Step 5: Set Access Token for further requests
 */
$oauth->setToken($get_access_token['oauth_token'],$get_access_token['oauth_token_secret']);

/**
 * Step 6: Test request.
 */
$oauth->fetch('https://oauth-api.beatport.com/catalog/3/' . $path . $qrystring);
$json = $oauth->getLastResponse();

header('Content-Type: application/json');

echo $json;

?>