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

## An example of how it works

As mentioned, a test has three phases:
1. Writing the test files to the directory in question
2. Doing a request (in advanced cases, more)
3. Interpreting the request.

As it turns out, for these purposes interpreting is in most cases dead simple. The response is examined and mapped into one of three possible results: *success*, *failure* or *inconclusive*.

As an example, lets see what goes on for the *canRewrite()* test:

### 1. The files

The following test files are used for the *rewrite* test:

**.htaccess**
```text
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^0\.txt$ 1\.txt [L]
</IfModule>
```

*0.txt*
```text
0
```

*1.txt*
```text
1
```

### 2. The request
A HTTP request is made to "0.txt"

### 3. The interpretation
- Map to *success*, if the body of the response is "1"
- Map to *failure*, if the body of the response is "0"
- Map to *failure*, if the status code of the response is "500" (as this probably means that the directive was forbidden)
- Otherwise map to *inconclusive* (something went wrong with running the test, so no conclusion could be reached. The most common reason would probably be a "403 forbidden")


## Running your own custom tests

The API provides the *customTest()* method for running custom tests easily, by just providing a definition. It isn't capable of handling complex interpretation, but it takes care of simple cases like *canRewrite()* in a breeze (for complex interpretation, you will need to extend `HtaccessCapabilityTester\Testers\AbstractTester`).

As an example, here is how the mapping used in *canRewrite()* is defined:

```php
$mapping = [
    ['success', 'body', 'equals', '1'],
    ['failure', 'body', 'equals', '0'],
    ['failure', 'statusCode', 'equals', '500'],
]
```

The list of mappings is read from the top until one of the conditions is met. The first line for example translates to "Map to success if the body of the response equals '1'". If none of the conditions are met, the result is automatically mapped to 'inconclusive'.

*canRewrite()* does not need to examine response headers, but this is possible too, like this:

```php
[
    ['success', 'headers', 'contains-key-value', 'Content-Type', 'image/gif'],
    ['inconclusive', 'headers', 'contains-key', 'Content-MD5'],
]
```

Here is a full example for replicating *canRewrite()*:

```php
$htaccessFile = <<<'EOD'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^0\.txt$ 1\.txt [L]
</IfModule>
EOD;

$test = [
    [
        'subdir' => 'rewrite-tester',
        'files' => [
            ['.htaccess', $htaccessFile],
            ['0.txt', "0"],
            ['1.txt', "1"]
        ],
        'request' => '0.txt',
        'interpretation' => [
            ['success', 'body', 'equals', '1'],
            ['failure', 'body', 'equals', '0'],
            ['failure', 'statusCode', 'equals', '500'],
        ]
    ]
];

$testResult = $hct->customTest($test);
```

Bonus info:
- It is possible to pass in an array of tests instead of just one test. The second test will only run if there is no match in the first, and so on
- You can make each test-definition conditional upon a test such as canRewrite(). To do so, add a "requirements" property like this: 'requirements' => `['canRewrite()']`.

Almost all of the build-in tests are written in this "language". You can look at the source code in the classes in "Testers" for inspiration.

## Installation
Require the library with *Composer*, like this:

```text
composer require rosell-dk/htaccess-capability-tester
```
