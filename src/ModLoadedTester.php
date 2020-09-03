<?php

namespace HtaccessCapabilityTester;

/**
 * Abstract class for testing if a given module is loaded and thus available in .htaccess
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class ModLoadedTester extends AbstractTester
{

    /* @var string A valid Apache module name (ie "rewrite") */
    protected $modName;

    public function __construct($baseDir, $baseUrl, $modName)
    {
        $this->modName = $modName;

        parent::__construct($baseDir, $baseUrl);
    }

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'mod-loaded-tester/' . $this->modName;
    }

    private function registerTestFiles1()
    {
        // Test files, method 1: Using ServerSignature
        // --------------------------------------------------
        $php = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $this->registerTestFile('test.php', $php, 'test-using-server-signature');

        $htaccess = <<<'EOD'
# The beauty of this trick is that ServerSignature is available in core.
# However, it requires PHP to check for the effect

ServerSignature Off
<IfModule mod_xxx.c>
ServerSignature On
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-server-signature');
    }

    private function registerTestFiles2()
    {
        // Test files, method 2: Using Rewrite
        // --------------------------------------------------

        $htaccess = <<<'EOD'
RewriteEngine On
<IfModule mod_xxx.c>
RewriteRule ^null\.txt$ 1.txt [L]
</IfModule>
<IfModule !mod_xxx.c>
RewriteRule ^null\.txt$ 0.txt [L]
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-rewrite');
        $this->registerTestFile('0.txt', "0", 'test-using-rewrite');
        $this->registerTestFile('1.txt', "1", 'test-using-rewrite');
        $this->registerTestFile(
            'null.txt',
            "Redirect failed even though rewriting has been proven to work. Strange!",
            'test-using-rewrite'
        );
    }

    private function registerTestFiles3()
    {
        // Test files, method 3: Using Response Header
        // --------------------------------------------------

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
Header set X-Response-Header-Test: 1
</IfModule>
<IfModule !mod_xxx.c>
Header set X-Response-Header-Test: 0
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-response-header');
        $this->registerTestFile('dummy.txt', "im just here", 'test-using-response-header');
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {
        $this->registerTestFiles1();
        $this->registerTestFiles2();
        $this->registerTestFiles3();
    }


    private function runTestUsingServerSignature()
    {
        $status = null;
        $info = '';

        $urlBase = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($urlBase . '/test-using-server-signature/test.php');

        if ($response->body == '') {
            // empty body means the HTTP request failed,
            // which we generally treat as inconclusive

            // As we don't expect any of the directives to be forbidden in our .htaccess,
            // We also treat 500 as an inconclusive result

            $status = null;
            $info = 'The test request failed with status code: ' . $response->statusCode .
                '. We interpret this as an inconclusive result';
        } else {
            if ($response->body == '1') {
                $status = true;
            } else {
                $status = false;
            }
        }
        return new TestResult($status, $info);
    }

    private function runTestUsingRewrite()
    {
        $status = null;
        $info = '';
        $urlBase = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($urlBase . '/test-using-rewrite/null.txt');
        if ($response->body == '1') {
            $status = true;
        } elseif ($response->body == '0') {
            $status = false;
        } else {
            // ok, thats weird, this should not be happening
            $info = $response->body;
        }
        return new TestResult($status, $info);
    }

    private function runTestUsingResponseHeader()
    {
        $status = null;
        $info = '';
        $urlBase = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($urlBase . '/test-using-response-header/dummy.txt');

        if (in_array('X-Response-Header-Test: 1', $response->headers)) {
            $status = true;
        } elseif (in_array('X-Response-Header-Test: 0', $response->headers)) {
            $status = false;
        } else {
            $status = null;
        }
        return new TestResult($status, $info);
    }

    /**
     *  Run the test.
     *
     *  @return TestResult   Returns a test result
     */
    public function run()
    {
        $hct = new HtaccessCapabilityTester($this->baseDir, $this->baseUrl);
        $enabledResult = $hct->htaccessEnabled();

        if ($enabledResult === false) {
            $status = false;
            $info = '.htaccess files are ignored altogether in this dir';
            $testResult = new TestResult($status, $info);
        } else {
            $testResult = $this->runTestUsingServerSignature();
            if (is_null($testResult->status)) {
                if ($hct->canRewrite()) {
                    // We got another shot!
                    // This one does not depend on grants for PHP.
                    $testResult = $this->runTestUsingRewrite();
                } else {
                    if ($hct->canSetResponseHeader()) {
                        // We got yet another shot!
                        $testResult = $this->runTestUsingResponseHeader();
                    }
                }
            }
        }

        return $testResult;
    }
}
