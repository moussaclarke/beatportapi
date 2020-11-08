# Beatport API PHP Class

A simple PHP class to query the Beatport Catalogo API.

The use case is for a server to server context - for example I used it to build a simple Beatport RSS feed for my label. NB: The class requests an access token each time it's instantiated and doesn't try to persist it to e.g. DB for later use.

It returns an array which you can then manipulate as you please.

## Aims

* Login and query the Beatport Catalog API
* Send back a simple array with the query results

## Requirements

* PHP 7.3+
* Beatport API Credentials (You'll need to request those from Beatport)
* Guzzle 7 (via composer)

## Install

```
composer require moussaclarke/beatportapi
```

## Usage

```
use MoussaClarke\BeatportApi;

// auth parameters
$parameters = [
  'client_id'=> 'CLIENT_ID', // Your Beatport Client Id
  'client_secret' => 'CLIENT_SECRET', // Your Beatport Client Secret
  ];

// query parameters
$query = [
  'label_id' => 'xyz', // a filter facet
  'resource' => 'releases', // The Beatport API resource to query
  'perPage' => '150' // Number of results per page
  ];

$api = new BeatportApi ($parameters); // initialise
$response = $api->queryApi ($query); // run the query
print_r ($response); // do something with response

```

You can check the [Beatport API documentation](https://oauth-api.beatport.com/) for which queries you can make and which parameters are required.

## Disclaimer

Totally and utterly alpha, and likely to break at any point. Not guaranteed to work as intended in any way, so use at your own risk.

## Todo

* Handle errors
* Support other bits of the API, e.g. curation

## Maintained

By [Moussa Clarke](https://github.com/moussaclarke/)

## Contribute

Feel free to submit bug reports, suggestions and pull requests. Alternatively just fork it and make your own thing.

## License
MIT

## Music
Outside of geekdom, I'm a DJ, producer and label manager, go check me out:

* [Moussa Clarke](http://www.moussaclarke.co.uk)
* [Glamour Punk](http://www.glamourpunk.co.uk)
