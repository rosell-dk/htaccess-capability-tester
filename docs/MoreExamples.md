

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

if ($hct->doesAddTypeWork()) {
    // AddType directive works
}

if ($hct->doesSetResponseHeaderWork()) {
    // "Header set" works
}
if ($hct->doesSetRequestHeaderWork()) {
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
    if ($hct->doesSetRequestHeaderWork()) {
        // "RequestHeader set" works
    }

} catch (\Exception $e) {
    // Probably permission problems.
    // We should probably notify someone
}
```
