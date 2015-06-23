# Beatport API PHP Class

A simple PHP class to query the Beatport API via Oauth.

I built this because I needed something Object Oriented and relatively unopinionated. This essentially just returns an array which you can then manipulate as you please (for example you could output JSON to use in your own API, build an RSS feed, make a webhook, or whatever else you want)

This is heavily based on the following people's work:

* [Beatport API Json Feed](https://github.com/fedegiust/Beatport-API-JSON-feed) by Federico Giust (I originally forked this repo as a starting point)
* [Beatport OAuth Connect w/ PECL](https://groups.google.com/forum/#!topic/beatport-api/sEpZUJkaSdo) by Tim Brandwijk (Federico Giust's script was based on this one)
* [Beatport OAuth Connect w/ PEAR](https://groups.google.com/forum/#!topic/beatport-api/sEpZUJkaSdo) by Christian Kolloch (Also based on Tim Brandwijk's script - I used this for the pear/http_oauth groundwork)

## Aims

* Login and query the Beatport API
* Abstract away the OAuth pain
* Send back a simple array with the results
* Don't rely on too many esoteric server-side extensions (e.g. PECL) which can be a non-starter on cheap/shared hosts

## Requirements

* PHP 5.4+ (Might work on earlier versions, but that's what I've been using)
* Beatport API Key and login details (You'll need to request those from Beatport)
* Pear's HTTP_OAuth (via composer)

## Install

* Run composer install to get the dependencies
* Include the class (See Todo, though)
* The file example.php is just a quick test/example - to run it you can either fill in your details into config.tmp.php and rename it to config.php, or alternatively comment out the config.php include line, and replace the constant names in example.php with your details.

## Usage

```
// login parameters
$parameters = array (
  'consumer'=> 'CONSUMERKEY', // Your Beatport API Key
  'secret' => 'SECRETKEY', // Your Beatport Secret Key
  'login' => 'BEATPORTLOGIN', // Your Beatport Login Name
  'password' => 'BEATPORTPASSWORD' // Your Beatport Password
  );

// query params
$query = array (
  'facets' => 'labelId:xyz', // The filter type
  'url' => 'releases', // The API Method
  'perPage' => '150' // Number of results per page
  );

$api = new BeatportApi ($parameters); // initialise
$response = $api->queryApi ($query); // run the query
print_r ($response); // do something with response

```

You can check the [Beatport API documentation](https://oauth-api.beatport.com/) for which queries you can make and which parameters are required, although they are currently untested beyond the above example, so your mileage may vary (for example I haven't even got "sortBy" to work just yet)

## Disclaimer

Totally and utterly alpha, and likely to break at any point. Not guaranteed to work as intended in any way, so use at your own risk.

## Todo

* Store the access token somewhere and re-use it until expiry - we shouldn't need to issue a new one for every single API query. Probably just a file in /data?
* Get some sanity into the variable / method names
* Add some proper error catching / messaging
* Tidy up the code to PSR-2 and comment it properly.
* Test and document other query types.
* Composer/Packagist

## Maintained

By [Moussa Clarke](https://github.com/moussaclarke/)

## Contribute

Would be cool to improve this, so feel free to submit bug reports, suggestions and pull requests. Can't guarantee I've got enough time to do much though!

## License
[WFTPL](http://www.wtfpl.net/), insofar as those other guys are cool with that.

## Music
Outside of geekdom, I'm a DJ, producer and label manager, go check me out:

* [Moussa Clarke](http://www.moussaclarke.co.uk)
* [Glamour Punk](http://www.glamourpunk.co.uk)





