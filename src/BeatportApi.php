<?php
/**
 * This is originally based on:
 * Beatport OAuth API by Federico Giust
 * Beatport OAuth Connect by Tim Brandwijk
 * Beatport OAuthConnect by Christian Kolloch
 *
 */

// Usage:
// $api = new Moussaclarke\BeatportApi (array $parameters); // initialise
// $response = $api->queryApi (array $query); // run the query
// echo $response; // do something with response

namespace MoussaClarke;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\TransferStats;

class BeatportApi
{

    private $client; // http client
    private $curl;
    private $logger; // a monolog instance for debugging
    private $callbackuri; // a callback uri

    public function __construct($parameters, $logger = NULL)
    {
        // parameter array consumer, secret, login, password, callback

        // Beatport credentials and callback uri
        $consumerkey    = $parameters["consumer"]; // Beatport Consumer Key
        $consumersecret = $parameters["secret"]; // Beatport Consumer Secret
        $beatportlogin  = $parameters["login"]; // Beatport Username
        $beatportpassword = $parameters["password"]; // Beatport Password
        $this->callbackuri = $parameters["callbackuri"]; //  callback uri
        
        // Logger instance if required
        $this->logger = $logger;

        // do the oauth dance
        $this->client      = $this->oAuthDance($consumerkey, $consumersecret, $beatportlogin, $beatportpassword);
    }

    private function buildQuery($parameters)
    {
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
            'path'      => $path,
            'qryarray'  => $qryarray,
        );

    }

    private function oAuthDance($consumerkey, $consumersecret, $beatportlogin, $beatportpassword)
    {

        // stack instance
        $stack = HandlerStack::create();
        $stack = $this->getLogger($stack); // add logger if need be

        // Beatport URLs
        $baseuri = 'https://oauth-api.beatport.com';

        // First Leg

        // Set up client, passing in stack as a reference
        $client = new Client(['base_uri' => $baseuri, 'auth' => 'oauth', 'handler' => &$stack]);

        // Create Oauth and pass it to the stack
        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret
        ]);

        $stack->push($oauth);

        // request the token
        $response = $client->post('identity/1/oauth/request-token',
            ['form_params' =>[
            'oauth_callback' => $this->callbackuri
            ]
            ]);

        // parse the response
        $params = urldecode((string) $response->getBody());
        parse_str($params); //oauth_token, oauth_token_secret, oauth_callback_confirmed


        // Second Leg

        // prepare the args
        $postargs= ['oauth_token' => $oauth_token, 'username' => $beatportlogin, 'password' => $beatportpassword, 'submit'=>'Login'];

        // submit credentials
        $response = $client->post('identity/1/oauth/authorize-submit',
            ['form_params' => $postargs,
            'on_stats' => function (TransferStats $stats) use (&$lastrequesturi) {
                $lastrequesturi = $stats->getEffectiveUri();
            }
            ]
            );

        // parse the callback request query string and put it into an array as it shouldn't over-write last params
        $params = $lastrequesturi->getQuery();
        $result = array();
        parse_str($params, $result);

        // we should check if the tokens match, crappy placeholder implementation for now, but whatevs
        if ($result['oauth_token'] != $oauth_token) {
            echo 'tokens dont match. aborting.';
            die();
        }

        // Third and final leg

        // lets create a new stack and add oauth with our temp token & token secret
        $stack = HandlerStack::create();
        $stack = $this->getLogger($stack); // add logger if need be

        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret,
            'token' => $result['oauth_token'],
            'token_secret' => $oauth_token_secret
        ]);

        $stack->push($oauth);

        // Let's get the final access token
        $response = $client->post('identity/1/oauth/access-token', 
            ['form_params' => [
            'oauth_verifier' => $result['oauth_verifier']
            ]
            ]);

        // And parse the response
        $params = urldecode((string) $response->getBody());
        parse_str($params); //oauth_token, oauth_token_secret, session_id, oauth_callback_confirmed

        // let's create new stack and oauth for subsequent requests
        $stack = HandlerStack::create();
        $stack = $this->getLogger($stack); // add logger if need be

        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret,
            'token' => $oauth_token,
            'token_secret' => $oauth_token_secret
        ]);

        $stack->push($oauth);

        return $client;

    }

    private function getLogger($stack)
    // this is in here for debugging purposes, you can ignore it
    {
        if ($this->logger) {

        $loggingmiddleware = Middleware::log(
                $this->logger,
                new MessageFormatter('{request} - {response}')
            );
        $stack->push($loggingmiddleware);
    }
    return $stack;
    }

    public function queryApi($parameters)
    {
        // parameters array with facets, sortBy, perPage, id, url, etc
        $query    = $this->buildQuery($parameters);
        $path     = $query['path'];
        $qryarray = $query['qryarray'];

        $response = $this->client->get('catalog/3/' . $path, ['query' => $qryarray]);

        $json = $response->getBody();

        return json_decode($json, true);

    }

}
