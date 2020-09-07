# API overview
This document is under development...

## Test methods in HtaccessCapabilityTester:

### `htaccessEnabled()`
Apache can be configured to ignore `.htaccess` files altogether. This method tests if the `.htaccess` file is processed at all

The method works by trying out a series of subtests until a conclusion is reached. It will never come out inconclusive.

How does it work?
- The first strategy is testing a series of features, such as `canRewrite()`. If any of them works, well, then the `.htaccess` must have been processed.
- Secondly, the `canSetServerSignature()` is tested. It tests the "ServerSignature" directive. If this test comes out negative, it is highly likely that the .htaccess has not been read, as the directive is a core directive. So we return *failure*.
- Lastly, if all other methods failed, we try calling `crashTest()` on an .htaccess file that we on purpose put syntax errors in. If it crashes, the .htaccess file must have been proccessed. If it does not crash, it has not.

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

### canSetResponseHeader()
Tests if setting a response header works using this simple test:

```yaml
subdir: set-response-header-tester
files:
    - filename: '.htaccess'
      content: |
          <IfModule mod_headers.c>
              Header set X-Response-Header-Test: test
          </IfModule>
    - filename: 'dummy.txt'
      content: 'they needed someone, so here i am'

request:
    url: 'dummy.txt'

interpretation:
    - ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', 'test'],
    - ['failure', 'statusCode', 'equals', '500']
```
