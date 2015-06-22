Beatport API OAuth
==================

With this PHP script you will be able to make api calls to the new Beatport API using OAuth and generate a JSON feed.

So for example you can use this on your server and using jQuery ajax to get a JSON object and do anything you need with it on your server.

Requirements
============

- PHP
- Beatport API Key and login details
- PHP OAuth (http://php.net/manual/en/book.oauth.php)
- MySQL (Optional if you want to log the api calls to a db)

Install
=======

Once you've installed PHP OAuth, upload this script to your server.

Edit config.tmp.php and fill in the constant declarations with the corresponding values

```
<?php
/**
* DB Log Settings
*/

/** MySQL database */
define('DB_NAME', '');

/** MySQL database username */
define('DB_USER', '');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname for example: localhost */
define('DB_HOST', '');

/**
* API Call OAuth Settings
*/

/** Website URL where beatport_api_callback.php is sitting  */
define('WEBSITE', '');

// Beatport API Consumer Key
define('CONSUMERKEY', '');

// Beatport API Secret Key
define('SECRETKEY', '');

// Beatport Login Details
define('BEATPORTLOGIN', '');

define('BEATPORTPASSWORD', '');

?>
```

Rename config.tmp.php to config.php

There are a couple of query string parameters that you can use to make different calls:

| Parameter | Description |
|-----------|-------------|
|facets     |facets to be used in the api call (for example: performerName:Richie+Hawtin) |
|sortBy     |how do you want the data to be sorted             |
|perPage    |how many results you want to get per page             |
|id         |track id (this can be used if no facets is being used)             |
|url        |path to be concatenated to the call if you want tracks, release, etc             |
|           | check Beatport API Documentation for more details)             |

You can start making calls to a url like this:

```
www.yourwebsite.com/beatportapi/beatport_api.php?facets=performerName:Richie+Hawtin&sortBy=publishDate%20desc&perPage=10&url=tracks
```

This will generate the api call to a URL like this:

```
https://oauth-api.beatport.com/catalog/3/tracks?facets=artistName%3ARichie+Hawtin&perPage=10&sortBy=publishDate+DESC
```

Which will get you a JSON object like this:

```
{
  "metadata": {
    "host": "api.beatport.com",
    "path": "/catalog/tracks",
    "query": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC",
    "page": 1,
    "perPage": 10,
    "count": 0,
    "totalPages": 0,
    "perPageOptions": [
      {
        "value": 50,
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=50&sortBy=publishDate+DESC"
      },
      {
        "value": 100,
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=100&sortBy=publishDate+DESC"
      },
      {
        "value": 150,
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=150&sortBy=publishDate+DESC"
      }
    ],
    "facets": {
      "fields": []
    },
    "appliedFacets": [],
    "dateFilters": [
      {
        "name": "today",
        "startDate": "2014-02-07",
        "endDate": "2014-02-07",
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC&publishDateStart=2014-02-07&publishDateEnd=2014-02-07",
        "applied": false
      },
      {
        "name": "yesterday",
        "startDate": "2014-02-06",
        "endDate": "2014-02-07",
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC&publishDateStart=2014-02-06&publishDateEnd=2014-02-07",
        "applied": false
      },
      {
        "name": "weekToDate",
        "startDate": "2014-01-31",
        "endDate": "2014-02-07",
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC&publishDateStart=2014-01-31&publishDateEnd=2014-02-07",
        "applied": false
      },
      {
        "name": "monthToDate",
        "startDate": "2014-01-07",
        "endDate": "2014-02-07",
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC&publishDateStart=2014-01-07&publishDateEnd=2014-02-07",
        "applied": false
      },
      {
        "name": "yearToDate",
        "startDate": "2013-02-07",
        "endDate": "2014-02-07",
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC&publishDateStart=2013-02-07&publishDateEnd=2014-02-07",
        "applied": false
      },
      {
        "custom": true,
        "name": "customRange",
        "applied": false,
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC"
      }
    ],
    "rangeFilters": {
      "bpm": {
        "custom": true,
        "rangeName": "bpm",
        "applied": false,
        "applyQuery": "facets%5B0%5D=artistName%3ARichie%2BHawtin&perPage=10&sortBy=publishDate+DESC"
      }
    },
    "spellcheck": null
  },
  "results": []
}
```

Feel free to extend this script to suit your needs.



