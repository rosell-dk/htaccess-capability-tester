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

    private function registerTestFilesServerSignature()
    {
        // Test files, method : Using ServerSignature
        // --------------------------------------------------
        // Requires (in order not to be inconclusive)
        // - Modules: None - its in core
        // - Override: All
        // - Directives: ServerSignature
        // - PHP?: Yes

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
# (it requires no modules and cannot easily be made forbidden)
# However, it requires PHP to check for the effect

ServerSignature Off
<IfModule mod_xxx.c>
ServerSignature On
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-server-signature');
    }

    private function registerTestFilesUsingRewrite()
    {
        // Test files, method: Using Rewrite
        // --------------------------------------------------
        // Requires (in order not to be inconclusive)
        // - Module: mod_rewrite
        // - Override: FileInfo
        // - Directives: RewriteEngine, RewriteRule and IfModule
        // - PHP?: No

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

    private function registerTestFilesUsingResponseHeader()
    {
        // Test files, method: Using Response Header
        // --------------------------------------------------
        // Requires (in order not to be inconclusive)
        // - Module: mod_headers
        // - Override: FileInfo
        // - Directives: Header and IfModule
        // - PHP?: No

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

    private function registerTestFilesUsingAddType()
    {
        // Test files, method: Using AddType
        // --------------------------------------------------
        //
        // Requires (in order not to be inconclusive)
        // - Module: mod_mime
        // - Override: FileInfo
        // - Directives: AddType and IfModule
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
AddType image/gif .test
</IfModule>
<IfModule !mod_xxx.c>
AddType image/jpeg .test
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-add-type');
        $this->registerTestFile('dummy.test', "im just here", 'test-using-add-type');
    }

    private function registerTestFilesUsingContentDigest()
    {
        // Test files, method: Using ContentDigest
        // --------------------------------------------------
        //
        // Requires (in order not to be inconclusive)
        // - Module: None - its in core
        // - Override: Options
        // - Directives: ContentDigest
        // - PHP?: No

        $htaccess = <<<'EOD'
<IfModule mod_xxx.c>
ContentDigest On
</IfModule>
<IfModule !mod_xxx.c>
ContentDigest Off
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->modName, $htaccess);
        $this->registerTestFile('.htaccess', $htaccess, 'test-using-content-digest');
        $this->registerTestFile('dummy.txt', "im just here", 'test-using-content-digest');
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {
        $this->registerTestFilesServerSignature();
        $this->registerTestFilesUsingRewrite();
        $this->registerTestFilesUsingResponseHeader();
        $this->registerTestFilesUsingAddType();
        $this->registerTestFilesUsingContentDigest();
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

    private function runTestUsingAddType()
    {
        $status = null;
        $info = '';
        $urlBase = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($urlBase . '/test-using-add-type/dummy.test');

        if (in_array('Content-Type: image/gif', $response->headers)) {
            $status = true;
        } else {
            if (in_array('Content-Type: image/jpeg', $response->headers)) {
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

    private function runTestUsingContentDigest()
    {
        $status = null;
        $info = '';

        $urlBase = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($urlBase . '/test-using-content-digest/dummy.txt');

        $headersHash = $response->getHeadersHash();

        if (isset($headersHash['Content-MD5'])) {
            $status = true;
        } else {
            $status = false;
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
            // The ServerSignature test requires:
            // - PHP
            // - nothing more (no modules, no overrides)

            $testResult = $this->runTestUsingServerSignature();

            if (is_null($testResult->status)) {
                // The ContentDigest test requires:
                // - Module: None - its in core
                // - Override: Options

                if ($hct->canContentDigest()) {
                    $testResult = $this->runTestUsingContentDigest();
                }
            }

            if (is_null($testResult->status)) {
                // The AddType test requires:
                // - Module: mod_mime     (very common)
                // - Override: FileInfo

                if ($hct->canAddType()) {
                    $testResult = $this->runTestUsingAddType();
                }
            }

            if (is_null($testResult->status)) {
                // The Rewrite test requires:
                // - Module: mod_rewrite  (pretty common)
                // - Override: FileInfo

                if ($hct->canRewrite()) {
                    $testResult = $this->runTestUsingRewrite();
                }
            }

            if (is_null($testResult->status)) {
                // The Response Header test requires:
                // - Module: mod_headers   (pretty common)
                // - Override: FileInfo

                if ($hct->canSetResponseHeader()) {
                    // We got yet another shot!
                    $testResult = $this->runTestUsingResponseHeader();
                }
            }
        }

        return $testResult;
    }
}
