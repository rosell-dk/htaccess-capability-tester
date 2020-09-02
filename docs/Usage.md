
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
