# IDEA:
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
  - [interprete500, status-code, equals, '500']   # inconclusive if innocent also crashes, otherwise failure
  - [inconclusive, status-code, equals, '403']

  - if: [status-code, equals, '500']
    then:
      - if: [doesInnocentCrash()]
        then: inconclusive
        else: failure
  - [inconclusive]



```

```php
[
  'interpretation' => [
    [
      'if' => ['body', 'equals', '1'],
      'then' => ['success']
    ],
    [
      'if' => ['body', 'equals', '0'],
      'then' => ['failure', 'no-effect']
    ],
    [
      'if' => ['status-code', 'equals', '500'],
      'then' => 'handle500()'
    ],
    [
      'if' => ['status-code', 'equals', '500'],
      'then' => 'handle500()'
    ]
  ]

```

```yaml

```

crashTestInnocent

handle500:
  returns "failure" if innocent request succeeds
  returns "inconclusive" if innocent request fails

handle403:
  if innocent request also 403, all requests probably does
  returns "failure" if innocent request succeeds
