# API overview
This document is under development...

## Test methods in HtaccessCapabilityTester:

### `htaccessEnabled()`
Apache can be configured to ignore `.htaccess` files altogether. This method tests if the `.htaccess` file is processed at all

The method works by trying out a series of subtests until a conclusion is reached. It will never come out inconclusive.

How does it work?
- The first strategy is testing a series of features, such as `canRewrite()`. If any of them works, well, then the `.htaccess` must have been processed.
- Secondly, the `canSetServerSignature()` is tested. The "ServerSignature" directive is special because it is in core and cannot be disabled with AllowOverride. If this test comes out as a failure, it is so *highly likely* that the .htaccess has not been processed, that we conclude that it has not.
- Lastly, if all other methods failed, we try calling `crashTest()` on an .htaccess file that we on purpose put syntax errors in. If it crashes, the .htaccess file must have been proccessed. If it does not crash, it has not. This last method is bulletproof - so why not do it first? Because it might generate an entry in the error log.

### `canContentDigest()`
```yaml
subdir: content-digest-tester
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

### `canRewrite()`
Tests if rewriting works using this simple test:

```yaml
subdir: rewrite-tester
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
subdir: directory-index-tester
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
subdir: set-request-header-tester
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
subdir: set-response-header-tester
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
