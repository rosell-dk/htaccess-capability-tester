# API overview
This document is under development...

## Test methods in HtaccessCapabilityTester:

All the test methods returns a test result, which is *true* for success, *false* for failure or *null* for inconclusive.

The tests have the following in common:
- If the server has been set up to ignore `.htaccess` files, the result will be *failure*.
- If the server has been set up to disallow the directive being tested (AllowOverride), the result is *failure* (both when configured to ignore and when configured to go fatal)
- A *403 Forbidden* results in *inconclusive*. Why? Because it could be that the server has been set up to forbid access to files matching a pattern that our test file unluckily matches. In most cases, this is unlikely, as most tests requests files with harmless-looking file extensions (often a "request-me.txt"). A few of the tests however requests a "test.php", which is more likely to be denied.

Most tests are implemented as a definition such as the one accepted in *customTest()*. This means that if you want it to work slightly differently, you can easily grab the code in the corresponding class in the *Testers* directory, make your modification and call *customTest()*. Those definitions are also available in this document, in YAML format (more readable).

<details><summary><b>doesAddTypeWork()</b></summary>
<p><br>
Tests if the *AddType* directive works.

Implementation (YAML definition):

```yaml
subdir: add-type
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_mime.c>
          AddType image/gif .test
      </IfModule>
  - filename: 'request-me.test'
    content: 'hi'
request:
  url: 'request-me.test'

interpretation:
 - ['success', 'headers', 'contains-key-value', 'Content-Type', 'image/gif']
 - ['failure', 'status-code', 'equals', '500']
 - ['inconclusive', 'status-code', 'not-equals', '200']
 - ['failure', 'headers', 'not-contains-key-value', 'Content-Type', 'image/gif']
```

</p>
</details>

<details><summary><b>doesContentDigestSetWork()</b></summary>
<p>

Implementation (YAML definition):

```yaml
subdir: content-digest
subtests:
  - subdir: on    
    files:
    - filename: '.htaccess'
      content: |
        ContentDigest On
    - filename: 'request-me.txt'
      content: 'hi'
    request:
      url: 'request-me.txt'
    interpretation:
      - ['failure', 'status-code', 'equals', '500'],
      - ['inconclusive', 'status-code', 'not-equals', '200'],    // calls the whole thing off
      - ['failure', 'headers', 'not-contains-key', 'Content-MD5'],

    - subdir: off
      files:
        - filename: '.htaccess'
          content: |
             ContentDigest Off
        - filename: 'request-me.txt'
          content: 'hi'
      request:
        url: 'request-me.txt'

      interpretation:
        - ['failure', 'status-code', 'equals', '500']
        - ['failure', 'headers', 'contains-key', 'Content-MD5']
        - ['inconclusive', 'status-code', 'not-equals', '200']
        - ['success', 'status-code', 'equals', '200']
```

</p>
</details>

<details><summary><b>doesPassingInfoFromRewriteToScriptThroughEnvWorkTester()</b></summary>
<p><br>
Say you have a rewrite rule that points to a PHP script and you would like to pass some information along to the PHP. Usually, you will just pass it in the query string. But this won't do if the information is sensitive. In that case, there are some tricks available. The trick being tested here tells the RewriteRule directive to set an environment variable, which in many setups can be picked up in the script.

Implementation (YAML definition):

```yaml
subdir: pass-info-from-rewrite-to-script-through-env
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_rewrite.c>

          # Testing if we can pass environment variable from .htaccess to script in a RewriteRule
          # We pass document root, because that can easily be checked by the script

          RewriteEngine On
          RewriteRule ^test\.php$ - [E=PASSTHROUGHENV:%{DOCUMENT_ROOT},L]

      </IfModule>
  - filename: 'test.php'
    content: |
      <?php

      /**
       *  Get environment variable set with mod_rewrite module
       *  Return false if the environment variable isn't found
       */
      function getEnvPassedInRewriteRule($envName) {
          // Environment variables passed through the REWRITE module have "REWRITE_" as a prefix
          // (in Apache, not Litespeed, if I recall correctly).
          // Multiple iterations causes multiple REWRITE_ prefixes, and we get many environment variables set.
          // We simply look for an environment variable that ends with what we are looking for.
          // (so make sure to make it unique)
          $len = strlen($envName);
          foreach ($_SERVER as $key => $item) {
              if (substr($key, -$len) == $envName) {
                  return $item;
              }
          }
          return false;
      }

      $result = getEnvPassedInRewriteRule('PASSTHROUGHENV');
      if ($result === false) {
          echo '0';
          exit;
      }
      echo ($result == $_SERVER['DOCUMENT_ROOT'] ? '1' : '0');

request:
  url: 'test.php'

interpretation:
  - ['success', 'body', 'equals', '1']
  - ['failure', 'body', 'equals', '0']
  - ['failure', 'status-code', 'equals', '500']
  - ['inconclusive', 'body', 'begins-with', '<?php']
  - ['inconclusive']
 ```

</p>
</details>

<details><summary><b>doesPassingInfoFromRewriteToScriptThroughRequestHeaderWork()</b></summary>
<p><br>
Say you have a rewrite rule that points to a PHP script and you would like to pass some information along to the PHP. Usually, you will just pass it in the query string. But this won't do if the information is sensitive. In that case, there are some tricks available. The trick being tested here tells the RewriteRule directive to set an environment variable which a RequestHeader directive picks up on and passes on to the script in a request header.

Implementation (YAML definition):

```yaml
subdir: pass-info-from-rewrite-to-script-through-request-header
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_rewrite.c>
          RewriteEngine On

          # Testing if we can pass an environment variable through a request header
          # We pass document root, because that can easily be checked by the script

          <IfModule mod_headers.c>
            RequestHeader set PASSTHROUGHHEADER "%{PASSTHROUGHHEADER}e" env=PASSTHROUGHHEADER
          </IfModule>
          RewriteRule ^test\.php$ - [E=PASSTHROUGHHEADER:%{DOCUMENT_ROOT},L]

      </IfModule>
  - filename: 'test.php'
    content: |
      <?php
      if (isset($_SERVER['HTTP_PASSTHROUGHHEADER'])) {
          echo ($_SERVER['HTTP_PASSTHROUGHHEADER'] == $_SERVER['DOCUMENT_ROOT'] ? 1 : 0);
          exit;
      }
      echo '0';

request:
  url: 'test.php'

interpretation:
  - ['success', 'body', 'equals', '1']
  - ['failure', 'body', 'equals', '0']
  - ['failure', 'status-code', 'equals', '500']
  - ['inconclusive', 'body', 'begins-with', '<?php']
  - ['inconclusive']
```

</p>
</details>

<details><summary><b>doesRewritingWork()</b></summary>
<p><br>
Tests if rewriting works.

Implementation (YAML definition):
```yaml
subdir: rewrite
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_rewrite.c>
          RewriteEngine On
          RewriteRule ^0\.txt$ 1\.txt [L]
      </IfModule>
  - filename: '0.txt'
    content: '0'
  - filename: '1.txt'
    content: '1'

request:
  url: '0.txt'

interpretation:
  - [success, body, equals, '1']
  - [failure, body, equals, '0']
  - [failure, status-code, equals, '500']
```

</p>
</details>

<details><summary><b>doesDirectoryIndexWork()</b></summary>
<p><br>
Tests if DirectoryIndex works.

Implementation (YAML definition):

```yaml
subdir: directory-index
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_dir.c>
          DirectoryIndex index2.html
      </IfModule>
  - filename: 'index.html'
    content: '0'
  - filename: 'index2.html'
    content: '1'

request:
  url: ''   # We request the index, that is why its empty

interpretation:
  - ['success', 'body', 'equals', '1']
  - ['failure', 'body', 'equals', '0']
  - ['failure', 'status-code', 'equals', '500']
  - ['failure', 'status-code', 'equals', '404']  # "index.html" might not be set to index
```

</p>
</details>

<details><summary><b>doesSetRequestHeaderWork()</b></summary>
<p><br>
Tests if a request header can be set using the *RequestHeader* directive.

Implementation (YAML definition):

```yaml
subdir: set-request-header
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_headers.c>
          # Certain hosts seem to strip non-standard request headers,
          # so we use a standard one to avoid a false negative
          RequestHeader set User-Agent "request-header-test"
      </IfModule>
  - filename: 'test.php'
    content: |
      <?php
      if (isset($_SERVER['HTTP_USER_AGENT'])) {
          echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? 1 : 0;
      } else {
          echo 0;
      }

request:
  url: 'test.php'

interpretation:
  - ['success', 'body', 'equals', '1']
  - ['failure', 'body', 'equals', '0']
  - ['failure', 'status-code', 'equals', '500']
  - ['inconclusive', 'body', 'begins-with', '<?php']
```

</p>
</details>

<details><summary><b>doesSetResponseHeaderWork()</b></summary>
<p><br>
Tests if setting a response header works using the *Header* directive.

Implementation (YAML definition):

```yaml
subdir: set-response-header
files:
  - filename: '.htaccess'
    content: |
      <IfModule mod_headers.c>
          Header set X-Response-Header-Test: test
      </IfModule>
  - filename: 'request-me.txt'
    content: 'hi'

request:
  url: 'request-me.txt'

interpretation:
  - [success, headers, contains-key-value, 'X-Response-Header-Test', 'test']
  - [failure, status-code, equals, '500']
  - [inconclusive, status-code, not-equals, '200']
  - [failure]
```

</p>
</details>

<details><summary><b>doesServerSignatureWork()</b></summary>
<p><br>
Tests if the *ServerSignature* directive works.

Implementation (YAML definition):
```yaml
subdir: server-signature
subtests:
  - subdir: on
    files:
    - filename: '.htaccess'
      content: |
        ServerSignature On
    - filename: 'test.php'
      content: |
      <?php
      if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
          echo 1;
      } else {
          echo 0;
      }
    request:
      url: 'test.php'
    interpretation:
      - ['inconclusive', 'status-code', 'equals', '403']
      - ['inconclusive', 'body', 'isEmpty']
      - ['inconclusive', 'status-code', 'not-equals', '200']
      - ['failure', 'body', 'equals', '0']

  - subdir: off
    files:
    - filename: '.htaccess'
      content: |
        ServerSignature Off
    - filename: 'test.php'
      content: |
      <?php
      if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
          echo 0;
      } else {
          echo 1;
      }
    request:
      url: 'test.php'
    interpretation:
      - ['inconclusive', 'body', 'isEmpty'],
      - ['success', 'body', 'equals', '1'],
      - ['failure', 'body', 'equals', '0'],
```

</p>
</details>

<details><summary><b>crashTest($rules, $subdir)</b></summary>
<p><br>
Test if some rules makes the server "crash" (respond with 500 Internal Server Error for requests to files in the folder).

Implementation (PHP):

```php
/**
 * @param string $htaccessRules  The rules to check
 * @param string $subSubDir      subdir for the test files. If not supplied, a fingerprint of the rules will be used
 */
public function __construct($htaccessRules, $subSubDir = null)
{
    if (is_null($subSubDir)) {
        $subSubDir = hash('md5', $htaccessRules);
    }

    $test = [
        'subdir' => 'crash-tester/' . $subSubDir,
        'subtests' => [
            [
                'subdir' => 'the-prospect',
                'files' => [
                    ['.htaccess', $htaccessRules],
                    ['request-me.txt', 'thanks'],
                ],
                'request' => 'request-me.txt',
                'interpretation' => [
                    ['success', 'status-code', 'not-equals', '500'],
                ]
            ],
            [
                'subdir' => 'the-innocent',
                'files' => [
                    ['.htaccess', '# I am no trouble'],
                    ['request-me.txt', 'thanks'],
                ],
                'request' => 'request-me.txt',
                'interpretation' => [
                    // The prospect crashed. But if the innocent crashes too, we cannot judge
                    ['inconclusive', 'status-code', 'equals', '500'],

                    // The innocent did not crash. The prospect is guilty!
                    ['failure'],
                ]
            ],
        ]
    ];

    parent::__construct($test);
}
```

</p>
</details>

<details><summary><b>htaccessEnabled()</b></summary>
<p><br>

Apache can be configured to ignore `.htaccess` files altogether. This method tests if the `.htaccess` file is processed at all

The method works by trying out a series of subtests until a conclusion is reached. It will never come out inconclusive.

How does it work?
- The first strategy is testing a series of features, such as `doesRewritingWork()`. If any of them works, well, then the `.htaccess` must have been processed.
- Secondly, the `doesServerSignatureWork()` is tested. The "ServerSignature" directive is special because it is in core and cannot be disabled with AllowOverride. If this test comes out as a failure, it is so *highly likely* that the .htaccess has not been processed, that we conclude that it has not.
- Lastly, if all other methods failed, we try calling `crashTest()` on an .htaccess file that we on purpose put syntax errors in. If it crashes, the .htaccess file must have been proccessed. If it does not crash, it has not. This last method is bulletproof - so why not do it first? Because it might generate an entry in the error log.

</p>
</details>
