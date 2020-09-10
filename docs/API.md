# API overview
This document is under development...

## Test methods in HtaccessCapabilityTester:

All the test methods returns a test result, which is *true* for success, *false* for failure or *null* for inconclusive.

The tests have the following in common:
- If the server has been set up to ignore `.htaccess` files, the result will be *failure*.
- If the server has been set up to disallow the directive being tested (AllowOverride), the result is *failure* (both when configured to ignore and when configured to go fatal)
- A *403 Forbidden* results in *inconclusive*. Why? Because it could be that the server has been set up to forbid access to files matching a pattern that our test file unluckily matches. In most cases, this is unlikely, as most tests requests files with harmless-looking file extensions (often a "request-me.txt"). A few of the tests however requests a "test.php", which is more likely to be denied.


### `canAddType()`
Tests if AddType directive works.

<details><summary>Implementation</summary>
<p>
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

### `canContentDigest()`
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
### `canPassInfoFromRewriteToScriptThroughRequestHeader()`

Say you have a rewrite rule that points to a PHP script and you would like to pass some information along to the PHP. Usually, you will just pass it in the query string. But this won't do if the information is sensitive. In that case, there are some tricks available. The trick being tested here sets tells the RewriteRule directive to set an environment variable which a RequestHeader directive picks up on and passes on to the script in a request header.

implementation:
```yaml
subdir: pass-env-through-request-header
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
'subdir' => 'pass-env-through-request-header',
'files' => [
    ['.htaccess', $htaccessFile],
    ['test.php', $phpFile],
],
'request' => 'test.php',
'interpretation' => [
    ['success', 'body', 'equals', '1'],
    ['failure', 'body', 'equals', '0'],
    ['failure', 'status-code', 'equals', '500'],
    ['inconclusive', 'body', 'begins-with', '<?php'],
    ['inconclusive']


### `canRewrite()`
Tests if rewriting works using this simple test:

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

### `canSetDirectoryIndex()`

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

### `canSetRequestHeader()`
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

### `canSetResponseHeader()`
Tests if setting a response header works using this simple test:

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

### `canSetServerSignature()`
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

### `htaccessEnabled()`
Apache can be configured to ignore `.htaccess` files altogether. This method tests if the `.htaccess` file is processed at all

The method works by trying out a series of subtests until a conclusion is reached. It will never come out inconclusive.

How does it work?
- The first strategy is testing a series of features, such as `canRewrite()`. If any of them works, well, then the `.htaccess` must have been processed.
- Secondly, the `canSetServerSignature()` is tested. The "ServerSignature" directive is special because it is in core and cannot be disabled with AllowOverride. If this test comes out as a failure, it is so *highly likely* that the .htaccess has not been processed, that we conclude that it has not.
- Lastly, if all other methods failed, we try calling `crashTest()` on an .htaccess file that we on purpose put syntax errors in. If it crashes, the .htaccess file must have been proccessed. If it does not crash, it has not. This last method is bulletproof - so why not do it first? Because it might generate an entry in the error log.
