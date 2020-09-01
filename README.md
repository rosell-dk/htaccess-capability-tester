# htaccess-capability-tester

[![Build Status](https://travis-ci.org/rosell-dk/htaccess-capability-tester.png?branch=master)](https://travis-ci.org/rosell-dk/htaccess-capability-tester)

Detect `.htaccess` capabilities through live tests.

NOTE: the API in the master branch is currently undergoing a lot of change

There are cases where the only way to to learn if a given `.htaccess` capability is enabled / supported on a system is by examining it "from the outside" through a HTTP request. This library is build to handle such testing easily.

This is what happens behind the scenes:
1. Some test files for a given test are put on the server. Typically these at least includes an `.htaccess` file. For many tests, a `test.php` file is also generated.
2. The test is triggered by doing a HTTP request (often to `test.php`)
3. The response is interpreted

To use the library, you must provide a path to where the test files are going to be put and an URL that they can be reached. Besides that, you just need to pick one of the tests that you want to run. There are tests for deciding if a module is loaded, if the .htaccess is completely ignored, etc.


## Usage

The following example runs the test designed to determine if the `RequestHeader` directive is available and working. There are many other tests in the library, but they are all used in this fashion:

```php
require 'vendor/autoload.php';
use HtaccessCapabilityTester\SetRequestHeaderTester;

try {
    $tester = new SetRequestHeaderTester($baseDir, $baseUrl);
    $testResult = SetRequestHeaderTester->run();

    if ($testResult->status === true) {
        // It absolutely works
    } elseif ($testResult->status === false) {
        // It absolutely does not work

    } elseif (is_null($testResult->status)) {
        // The test was inconclusive.
    }

} catch (\Exception $e) {
    // The test throws an Exception if it cannot run due to serious issues
    // - if the test files cannot be written to the directory
}
```

## How is this achieved?

At the heart of each test are the test files. As an illustration, here are the files for the RequestHeader test:

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

Simple, right? The rest is just about putting the files at the location, doing the HTTP request and of course interpreting the result. In this case, the interpreting is easy. If `test.php` responds with "1", it must mean that setting the RequestHeader in the .htaccess worked. If it responds with "0", it means it did not work. If it responds with a 500 Internal Server Error, it (most likely) means that the RequestHeader directive has been disallowed, which also means it didn't work.


## Installation
Require the library with *Composer*, like this:

```text
composer require rosell-dk/htaccess-capability-tester
```

## Available tests:

- *HtaccessEnabledTester* : Tests if .htaccess are read at all.
- *ModEnvLoadedTester* : Tests if *mod_env* is loaded
- *ModHeadersLoadedTester* : Tests if *mod_headers* is loaded
- *RewriteTester* : Tests if rewriting works.
- *SetRequestHeaderTester* : Tests if setting request headers in `.htaccess` works.
- *GrantAllCrashTester* : Tests that `Require all granted` works (that it does not result in a 500 Internal Server Error)
- *PassEnvThroughRequestHeaderTester* : Tests if passing an environment variable through a request header in an `.htaccess` file works.
- *PassEnvThroughRewriteTester*: Tests if passing an environment variable by setting it in a REWRITE in an `.htaccess` file works.

### Running your own test
It is not to define your own test by extending the "AbstractTester" class. You can use the code in one of the provided testers as a template (ie `SetRequestHeaderTester.php`).

If you are in need of a test that discovers if an `.htaccess` causes an 500 Internal Server error, it is even more simple: Just extend the *AbstractCrashTester* class and implement the *getHtaccessToCrashTest()* method (see `GrantAllCrashTester.php`)

### Using another library for making the HTTP request
This library simply uses `file_get_contents` to make HTTP requests. It can however be set to use another library. Use the `setHTTPRequestor` method for that. The requester must implement `HTTPRequesterInterface` interface, which simply consists of a single method: `makeHTTPRequest($url)`
