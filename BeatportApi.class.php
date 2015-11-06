<?php
/**
 * This is heavily based on:
 * Beatport OAuth API by Federico Giust
 * Beatport OAuth Connect by Tim Brandwijk
 * Beatport OAuthConnect by Christian Kolloch
 *
 */

// Usage:
// $api = new BeatportApi (array $parameters); // initialise
// $response = $api->queryApi (array $query); // run the query
// echo $response; // do something with response

class BeatportApi {

    private $oauth;

    public function __construct($parameters) {
        // parameter array consumer, secret, login, password

        // Beatport URLs.
        $conskey        = $parameters["consumer"]; // Beatport Consumer Key
        $conssec        = $parameters["secret"]; // Beatport Consumer Secret
        $beatport_login = $parameters["login"];
        ; // Beatport Username
        $beatport_password = $parameters["password"]; // Beatport Password
        $this->oauth       = $this->oAuthDance($conskey, $conssec, $beatport_login, $beatport_password);

    }

    private function buildQuery($parameters) {
        // generate the query from parameters array - facets, sortBy, perPage, id, url, etc
        // todo: some error checking (e.g. have we had minimum params?) // error feedback
        // otherwise, api itself should handle validation, but we need to feedback any api error response

        $path = $parameters['url']; // this is the API method, e.g. tracks, releases etc
        unset($parameters['url']); // get rid of it as it's not a query param

        //initialise for the iteration
        $qryarray  = array();
        $qrystring = '';
        $i         = 0;

        // iterate through and build the query vars
        foreach ($parameters as $name => $value) {
            $qrystring .= $i == 0 ? '?' : '&'; // ? on first param, & for rest
            $qrystring .= $name . "=" . urlencode($value);
            $qryarray[$name] = $value;
            $i++;
        }

        return array(
            'qrystring' => $qrystring,
            'path' => $path,
            'qryarray' => $qryarray
        );

    }

    private function oAuthDance($conskey, $conssec, $beatport_login, $beatport_password) {

        /* might need some try/catch e.g. catch (Exception $e) {
        $content = $e->getMessage();
        echo $content;
        }
        */

        // Beatport URLs
        $req_url        = 'https://oauth-api.beatport.com/identity/1/oauth/request-token';
        $authurl        = 'https://oauth-api.beatport.com/identity/1/oauth/authorize';
        $auth_submiturl = 'https://oauth-api.beatport.com/identity/1/oauth/authorize-submit';
        $acc_url        = 'https://oauth-api.beatport.com/identity/1/oauth/access-token';


        $http_request = new HTTP_Request2(null, HTTP_Request2::METHOD_GET, array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false
        ));
        $http_request->setHeader('Accept-Encoding', '.*');

        $consumer_request = new HTTP_OAuth_Consumer_Request ();
        $consumer_request->accept($http_request);

        $oauth = new HTTP_OAuth_Consumer($conskey, $conssec);
        $oauth->accept($consumer_request);

        /**
         * Step 2: Set Request Token to log in
         */
        $request_token_info         = $oauth->getRequestToken($req_url);
        $oauth_request_token        = $oauth->getToken();
        $oauth_request_token_secret = $oauth->getTokenSecret();

        /**
         * Step 3: Use request token to log in and authenticate for 3-legged auth.
         */

        $post_string        = 'oauth_token=' . $oauth_request_token . '&username=' . $beatport_login . '&password=' . $beatport_password . '&submit=Login';
        $curl_connection_bp = curl_init();
        curl_setopt($curl_connection_bp, CURLOPT_URL, $auth_submiturl);
        curl_setopt($curl_connection_bp, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl_connection_bp, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT6.0; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
        curl_setopt($curl_connection_bp, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl_connection_bp, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection_bp, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl_connection_bp, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl_connection_bp, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection_bp, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_connection_bp, CURLOPT_VERBOSE, false);
        curl_setopt($curl_connection_bp, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($curl_connection_bp, CURLOPT_REFERER, $curl_connection_bp);
        curl_setopt($curl_connection_bp, CURLOPT_FAILONERROR, 0);
        curl_setopt($curl_connection_bp, CURLOPT_POST, true);
        curl_setopt($curl_connection_bp, CURLOPT_POSTFIELDS, $post_string);

        $beatport_response = curl_exec($curl_connection_bp);

        /**
         * Step 4: Use verifier string to request and set the Access Token
         */
        $oauth_exploded = array();
        parse_str($beatport_response, $oauth_exploded);
        curl_close($curl_connection_bp);
        $oauth->getAccessToken('https://oauth-api.beatport.com/identity/1/oauth/access-token', $oauth_exploded['oauth_verifier']);

        return $oauth;

    }

    public function queryApi($parameters) {
        // parameters array with facets, sortBy, perPage, id, url, etc
        $query    = $this->buildQuery($parameters);
        $path     = $query['path'];
        $qryarray = $query['qryarray'];

        $request  = $this->oauth->sendRequest('https://oauth-api.beatport.com/catalog/3/' . $path, $qryarray);

        $json     = $request->getBody();

        return json_decode($json, true);

    }

}
?>