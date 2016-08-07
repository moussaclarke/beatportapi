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
    private $logger; // a monolog instance for debugging

    public function __construct($parameters, $logger = null)
    {
        // parameter array consumer, secret, login, password, callback

        // Beatport credentials and callback uri
        $consumerkey       = $parameters["consumer"]; // Beatport Consumer Key
        $consumersecret    = $parameters["secret"]; // Beatport Consumer Secret
        $beatportlogin     = $parameters["login"]; // Beatport Username
        $beatportpassword  = $parameters["password"]; // Beatport Password

        // pass in monolog Logger instance if required
        $this->logger = $logger;

        // do the oauth dance
        $this->client = $this->oAuthDance($consumerkey, $consumersecret, $beatportlogin, $beatportpassword);
    }

    public function queryApi($parameters)
    {
        // parameters array with facets, sortBy, perPage, id, url, etc
        $method = $parameters['method']; // this is the API method, e.g. tracks, releases etc
        unset($parameters['method']); // unset it as it's not a query param

        // make the api call
        $response = $this->client->get('catalog/3/' . $method, ['query' => $parameters]);

        // get the response
        $json = $response->getBody();

        // return an array
        return json_decode($json, true);

    }

    private function oAuthDance($consumerkey, $consumersecret, $beatportlogin, $beatportpassword)
    {

        // Beatport URLs
        $baseuri = 'https://oauth-api.beatport.com';

        // First Leg

        // Create Oauth instance and get the stack
        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret,
        ]);

        $stack=$this->getStack ($oauth);

        // Set up http client, passing in stack as a reference
        $client = new Client(['base_uri' => $baseuri, 'auth' => 'oauth', 'handler' => &$stack]);

        // request the token, with out of bound callback, so no redirect
        $response = $client->post('identity/1/oauth/request-token',
            ['form_params' => [
                'oauth_callback' => 'oob',
            ],
            ]);

        // parse the response
        $params = urldecode((string) $response->getBody());
        $result=[];
        parse_str($params, $result); //oauth_token, oauth_token_secret, oauth_callback_confirmed

        // Second Leg

        // prepare the args
        $postargs = ['oauth_token' => $result['oauth_token'], 'username' => $beatportlogin, 'password' => $beatportpassword, 'submit' => 'Login'];

        // submit credentials
        $response = $client->post('identity/1/oauth/authorize-submit',
            ['form_params' => $postargs]
        );

        // parse the callback request query string and put it into a different array so it doesn't over-write last params
        $params = urldecode((string) $response->getBody());
        $result2=[];
        parse_str($params, $result2); // oauth_token, oauth_verifier

        // we should check if the tokens match, crappy placeholder implementation for now, but whatevs
        if ($result['oauth_token'] != $result2['oauth_token']) {
            echo 'tokens dont match. aborting.';
            die();
        }

        // Third and final leg

        // lets create a new oauth with our temp token & token secret & update the stack
        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret,
            'token'           => $result2['oauth_token'],
            'token_secret'    => $result['oauth_token_secret'],
        ]);

        $stack=$this->getStack ($oauth);

        // Let's get the final access token
        $response = $client->post('identity/1/oauth/access-token',
            ['form_params' => [
                'oauth_verifier' => $result2['oauth_verifier'],
            ],
            ]);

        // And parse the response
        $params = urldecode((string) $response->getBody());
        $result=[];
        parse_str($params, $result); //oauth_token, oauth_token_secret, session_id, oauth_callback_confirmed

        // let's create final oauth /stack for subsequent requests
        $oauth = new Oauth1([
            'consumer_key'    => $consumerkey,
            'consumer_secret' => $consumersecret,
            'token'           => $result['oauth_token'],
            'token_secret'    => $result['oauth_token_secret'],
        ]);

        $stack=$this->getStack ($oauth);

        return $client;

    }

    private function getStack ($oauth)
    {
        // send back a handlerstack instance 
        $stack = HandlerStack::create();
        $stack = $this->getLogger($stack); // get logger if exists
        $stack->push($oauth);
        return $stack;
    }

    private function getLogger($stack)
    // this is in here for dev/debugging purposes
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

}
