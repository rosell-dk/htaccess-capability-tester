# htaccess-capability-tester

[![Build Status](https://travis-ci.org/rosell-dk/htaccess-capability-tester.png?branch=master)](https://travis-ci.org/rosell-dk/htaccess-capability-tester)

Detect `.htaccess` capabilities through live tests.

NOTE: the API in the master branch is currently undergoing a lot of change

There are cases where the only way to to learn if a given `.htaccess` capability is enabled / supported on a system is by examining it "from the outside" through a HTTP request. This library is build to handle such testing easily.

This is what happens behind the scenes:
1. Some test files for a given test are put on the server. Typically these at least includes an `.htaccess` file. For many tests, a `test.php` file is also generated.
2. The test is triggered by doing a HTTP request (often to `test.php`)
3. The response is interpreted

## Usage

To use the library, you must provide a path to where the test files are going to be put and an URL that they can be reached. Besides that, you just need to pick one of the tests that you want to run.

```php
require 'vendor/autoload.php';
use HtaccessCapabilityTester\HtaccessCapabilityTester;

$hct = new HtaccessCapabilityTester($baseDir, $baseUrl);

if ($hct->moduleLoaded('headers')) {
    // mod_headers has been tested functional in a real .htaccess
}
if ($hct->canRewrite()) {
    // rewriting works
}
if ($hct->htaccessEnabled() === false) {
    // Apache has been configured to ignore .htaccess files
}

```

## How is this achieved?

At the heart of each test are the test files. As an illustration, here are the files for the test that examines if the RequestHeader directive works:

**.htaccess**
```
<IfModule mod_headers.c>
    RequestHeader set User-Agent "request-header-test"
</IfModule>```
```

**test.php**
```php
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? '1' : '0';
} else {
    echo '0';
}
```

Simple, right?


## More examples of what you can test:

```php

require 'vendor/autoload.php';
use HtaccessCapabilityTester\HtaccessCapabilityTester;

$hct = new HtaccessCapabilityTester($baseDir, $baseUrl);

$rulesToCrashTest = <<<'EOD'
<ifModule mod_rewrite.c>
  RewriteEngine On
</ifModule>
EOD;
if ($hct->crashTest($rulesToCrashTest)) {
    // The rules at least did not cause requests to anything in the folder to "crash".
    // (even simple rules like the above can make the server respond with a
    //  500 Internal Server Error - see "docs/TheManyWaysOfHtaccessFailure.md")
}

if ($hct->canSetResponseHeader()) {
    // "Header set" works
}
if ($hct->canSetRequestHeader()) {
    // "RequestHeader set" works
}

// Note that the tests returns null if they are inconclusive
$testResult = $hct->htaccessEnabled();
if (is_null($testResult)) {
    // Inconclusive!
    // Perhaps a 403 Forbidden?
    // You can get a bit textual insight by using:
    // $hct->infoFromLastTest
}

// Also note that an exception will be thrown if test files cannot be created.
// You might want to wrap your call in a try-catch statement.
try {
    if ($hct->canSetRequestHeader()) {
        // "RequestHeader set" works
    }

} catch (\Exception $e) {
    // Probably permission problems.
    // We should probably notify someone
}
```

## Installation
Require the library with *Composer*, like this:

```text
composer require rosell-dk/htaccess-capability-tester
```
