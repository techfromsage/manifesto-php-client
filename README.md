manifesto-php-client
====================

A PHP client to manage the creation and access to archives generated through Manifesto

## Getting Started

Install the module via composer, by adding the following to your projects ``composer.json``

```javascript
{
    "repositories":[
        {
            "type": "vcs",
            "url": "https://github.com/talis/manifesto-php-client"
        },
    ],
    "require" :{
        "talis/manifesto-client" : "0.2"
    }
}
```
then update composer:

```bash
$ php composer.phar update
```

Usage
-----

```php

// Create a Manifest

$manifest = new \Manifesto\Manifest()

// You can also pass a boolean to put it in 'safe mode' to ensure that the file count is what is expected

$manifest->setFormat(FORMAT_ZIP); // TODO, figure out how to namespace these constants

$file1 = array(
    "type" => FILE_TYPE_S3,
    "container" => "myBucket",
    "file" => "/path/to/file1.txt",
    "destinationPath" => "/FOO/BAR/BAZ.txt"
);

// Add this to the manifest
$manifest->addFile($file1);

$file2 = array(
    "type" => FILE_TYPE_CF,
    "container" => "CloudFileBucket",
    "file" => "/path/to/file2.txt" // Will output to /path/to/file2.txt
);

$manifest->addFile($file2);

// This is the minimum required
$file3 = array(
    "file" => "/path/to/file1.txt",
);
$manifest->addFile($file3);

$personaOptions = array(
    'persona_host' => 'http://persona',
    'persona_oauth_route' => '/oauth/tokens',
    'tokencache_redis_host' => 'localhost',
    'tokencache_redis_port' => 6379,
    'tokencache_redis_db' => 2
);

$client = new \Manifesto\Client($manifestBaseUrl, $personaOptions);

try
{
    $archive = $client->requestArchive($manifest, "client id", "client secret");

    $archive->getId();

    => 1234

    $archive->getStatus();

    => "Accepted"

}
catch(\Exception $e)
{

    ...
}
```

Tests
-----
```
$ npm install
$ grunt composer:install
$ grunt test
```