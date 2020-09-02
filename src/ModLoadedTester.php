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
    protected $moduleName;

    public function __construct($baseDir, $baseUrl, $moduleName)
    {
        $this->moduleName = $moduleName;

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
        return 'mod-loaded-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {


        $php = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $this->registerTestFile('test.php', $php, $this->moduleName);

        $htaccess = <<<'EOD'
# The beauty of this trick is that ServerSignature is available in core.

ServerSignature Off
<IfModule mod_xxx.c>
ServerSignature On
</IfModule>
EOD;

        $htaccess = str_replace('mod_xxx', 'mod_' . $this->moduleName, $htaccess);

        $this->registerTestFile('.htaccess', $htaccess, $this->moduleName);
    }

    /**
     *  Run the test.
     *
     *  @return TestResult   Returns a test result
     */
    public function run()
    {
        $status = null;
        $info = '';

        $enabledTester = new HtaccessEnabledTester($this->baseDir, $this->baseUrl);
        $enabledResult = $enabledTester->run();

        if ($enabledResult->status === false) {
            $status = false;
            $info = '.htaccess files are ignored altogether in this dir';
        } else {
            $url = $this->baseUrl . '/' . $this->subDir . '/' . $this->moduleName . '/test.php';
            $response = $this->makeHTTPRequest($url);

            if ($response->body == '') {
                // empty body means the HTTP request failed,
                // which we generally treat as inconclusive

                $status = null;
                $info = 'The test request failed with status code: ' . $response->statusCode .
                    '. We interpret this as an inconclusive result';

                $info .= '(url: ' . $url . ')';

                // As we don't expect any of the directives to be forbidden in our .htaccess,
                // We also treat 500 as an inconclusive result
            } else {
                if ($response->body == '1') {
                    $status = true;
                } else {
                    $status = false;
                }
            }
        }



        return new TestResult($status, $info);
    }
}
