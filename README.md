# htaccess-capability-tester

***This library is under creation***

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

To for example run the request header test, do this:

```php
use HtaccessCapabilityTester\Testers\SetRequestHeaderTester;

$tester = new SetRequestHeaderTester($baseDir, $baseUrl);
$testResult = $tester->runTest();
```

The library currently supports the following tests:

- *SetRequestHeaderTester* : Tests if setting request headers in `.htaccess` works.
- *RewriteTester* : Tests if rewriting works.

The following is on the way:
- A test for examining if `Require all granted` works or results in 500 Internal Server Error
- A test for examining if setting an environment variable in a rewrite rule works


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
