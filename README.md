# htaccess-capability-tester

[![Build Status](https://travis-ci.org/rosell-dk/htaccess-capability-tester.png?branch=master)](https://travis-ci.org/rosell-dk/htaccess-capability-tester)

Detect if a given `.htaccess` feature works on the system through a live test.

There are cases where the only way to to learn if a given `.htaccess` capability is enabled / supported on a system is through a HTTP request. This library is build to handle such testing easily.

This is what happens behind the scenes:
1. Some test files for a given test are put on the server. Typically these includes an `.htaccess` file and a `test.php` file
2. The test is triggered by doing a HTTP request to `test.php`

As an example of how the test files works, here are the files generated for determining if setting a request header in a .htaccess file works:

**.htaccess**
```
<IfModule mod_headers.c>
    RequestHeader set User-Agent "request-header-test"
</IfModule>```
```

**test.php**
```php
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? 1 : 0;
} else {
    echo 0;
}
```

## Usage:

### Running one of the provided tests:
To for example run the request header test, do this:

```php
use HtaccessCapabilityTester\Testers\SetRequestHeaderTester;

$tester = new SetRequestHeaderTester($baseDir, $baseUrl);

$testResult = $tester->runTest();

```
PS: Notice that `runTest()` throws an exception if the test files cannot be created.

The library currently supports the following tests:

- *RewriteTester* : Tests if rewriting works.
- *SetRequestHeaderTester* : Tests if setting request headers in `.htaccess` works.
- *GrantAllNotCrashTester* : Tests that `Require all granted` works (that it does not result in a 500 Internal Server Error)
- *PassEnvThroughRequestHeaderTester* : Tests if passing an environment variable through a request header in an `.htaccess` file works.
- *PassEnvThroughRewriteTester*: Tests if passing an environment variable by setting it in a REWRITE in an `.htaccess` file works.

### Running your own test
It is not to define your own test by extending the "Testers/AbstractTester" class. You can use the code in one of the provided testers as a template (ie `Testers/SetRequestHeaderTester.php`).

If you are in need of a test that discovers if an `.htaccess` causes an 500 Internal Server error, it is even more simple: Just extend the *AbstractCrashTester* class and implement the *getHtaccessToCrashTest()* method (see `Testers/GrantAllCrashTester.php`)


## Full example:
```php
require 'htaccess-capability-tester/vendor/autoload.php';

use HtaccessCapabilityTester\Testers\SetRequestHeaderTester;

// Where to put the test files
$baseDir = __DIR__ . '/live-tests';

// URL for running the tests
$baseUrl = 'http://hct0/live-tests';

$tester = new SetRequestHeaderTester($baseDir, $baseUrl);

$testResult = $tester->runTest();

if ($testResult === true) {
    echo 'the tested feature works';
} elseif ($testResult === false) {
    echo 'the tested feature does not work';
} elseif ($testResult === null) {
    echo 'the test did not reveal if the feature works or not';
}
```
