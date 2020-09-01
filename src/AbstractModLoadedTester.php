<?php

namespace HtaccessCapabilityTester;

/**
 * Abstract class for testing if a given module is loaded and thus available in .htaccess
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
abstract class AbstractModLoadedTester extends AbstractTester
{

    /**
     * Child classes must implement this method, which tells which module
     * should be tested (ie "headers", "rewrite" or such)
     *
     * @return  string  The module to test (ie "headers", "rewrite" or such)
     */
    abstract public function moduleToTest();

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'mod-' . $this->moduleToTest() . '-loaded-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {

        $file = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $this->registerTestFile('test.php', $file);

        $file = <<<'EOD'
# The beauty of this trick is that ServerSignature is available in core.

ServerSignature Off
<IfModule mod_xxx.c>
ServerSignature On
</IfModule>
EOD;

        $file = str_replace('mod_xxx', 'mod_' . $this->moduleToTest(), $file);

        $this->registerTestFile('.htaccess', $file);
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
            $responseOn = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/on/test.php');
            $responseOff = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/off/test.php');

            if ($responseOn->body == '') {
                // empty body means the HTTP request failed,
                // which we generally treat as inconclusive

                $status = null;
                $info = 'The test request failed with status code: ' . $responseOn->statusCode .
                    '. We interpret this as an inconclusive result';

                // As we don't expect any of the directives to be forbidden in our .htaccess,
                // We also treat 500 as an inconclusive result
            } else {
                if (($responseOn->body == '1') && ($responseOff->body == '0')) {
                    $status = true;
                } else {
                    $status = false;
                }
            }
        }



        return new TestResult($status, $info);
    }
}
