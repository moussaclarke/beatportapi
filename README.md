# Beatport API PHP Class

A simple PHP class to query the Beatport API.

I built this because I needed something Object Oriented and relatively unopinionated. This essentially just returns an array which you can then manipulate as you please (for example you could output JSON to use in your own API, build an RSS feed, IFTTT webhooks or whatever else you want)

This is heavily based on the following people's work:
* Beatport OAuth API by Federico Giust (I originally forked this)
* Beatport OAuth Connect by Tim Brandwijk (First one was based on this)
* Beatport OAuthConnect by Christian Kolloch (I used this for the Http_oauth groundwork)

## Requirements

* PHP 5.*
* Beatport API Key and login details
* Pear's HTTP_OAuth (via composer)

## Install

* Upload it and include the class (See Todo, though)
* To run the test, you can either fill in your details into config.tmp.php and rename it to config.php, or alternatively comment out the config.php include and replace the constant names in index.php with your details.

## Usage

```
$parameters = array (
  'consumer'=> CONSUMER,
  'secret' => SECRET,
  'login' => LOGIN,
  'password' => PASSWORD
  );

$query = array (
  'facets' => 'labelId:xyz',
  'url' => 'releases',
  'perPage' => '150'
  );

$api = new BeatportApi (array $parameters); // initialise
$response = $api->queryApi (array $query); // run the query
echo $response; // do something with response

?>
```

You can check the [Beatport API documentation](https://oauth-api.beatport.com/) for which queries you can make, although they are currently untested beyond the above, so your mileage may vary (for example I haven't got "sortBy" to work just yet)

## Todo

* Store the access token and re-use it until expiry - it shouldn't need to be issued on every single API query. Probably just a file in /data?
* Get some sanity into the variable / method names
* Add some proper error catching / messaging
* Tidy up the code to PSR-2 and comment it properly.
* Test and document other query types.
* Composer/Packagist

## Contribute

Would be cool to improve this, so feel free to submit bug reports, suggestions and pull requests. Can't guarantee I've got much time to do anything about it.

## Author
[Moussa Clarke](http://linkedin.com/moussaclarke). With thanks to the above.

## Music
Outside of geekdom, I'm a DJ, producer and label manager, go check it out:
[Moussa Clarke](http://www.moussaclarke.co.uk)
[Glamour Punk](http://www.glamourpunk.co.uk)

## License
[WFTPL](http://www.wtfpl.net/), insofar as those other guys are cool with that.





