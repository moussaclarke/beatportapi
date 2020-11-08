<?php
// Usage:
// $api = new Moussaclarke\BeatportApi (array $parameters); // initialise
// $response = $api->queryApi (array $query); // run the query
// echo $response; // do something with response

namespace MoussaClarke;

use GuzzleHttp\Client;

class BeatportApi
{
    private $client; // http client
    private $accessToken; // the access Token
    const BEATPORT_URL = "https://api.beatport.com/v4/";

    public function __construct(array $parameters)
    {
        // Beatport credentials
        $clientId     = $parameters["client_id"]; // Beatport Client Id
        $clientSecret = $parameters["client_secret"]; // Beatport Client Secret
        $this->client = new Client([
            'base_uri' => self::BEATPORT_URL,
        ]);
        $this->accessToken = $this->getAccessToken($clientId, $clientSecret);
    }

    public function queryApi($parameters)
    {
        // parameters array
        $resource = $parameters['resource']; // this is the resource, e.g. tracks, releases etc
        unset($parameters['resource']); // unset it as it's not a query param

        $args = [
            'query'   => $parameters,
            'headers' => ['Authorization' => "Bearer " . $this->accessToken],
        ];

        // make the api call
        $response = $this->client->request('GET', 'catalog/' . $resource, $args);

        // get the response
        $json = (string) $response->getBody();

        // return an array
        return json_decode($json, true);
    }

    private function getAccessToken($clientId, $clientSecret)
    {
        $args = [
            'form_params' => [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'grant_type'    => 'client_credentials',
            ],
        ];
        $response = $this->client->request('POST', 'auth/o/token/', $args);
        // TODO: handle errors
        $json   = (string) $response->getBody();
        $result = json_decode($json, true);
        return $result['access_token'];
    }
}
