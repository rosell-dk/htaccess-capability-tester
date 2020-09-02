<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if rewriting works at the tested location.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class HtaccessEnabledTester extends AbstractTester
{

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'htaccess-enabled-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {

        $htaccessFileOn = <<<'EOD'
ServerSignature On
EOD;

        $htaccessFileOff = <<<'EOD'
ServerSignature Off
EOD;

        $phpOn = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $phpOff = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 0;
} else {
    echo 1;
}
EOD;

        $this->registerTestFile('.htaccess', $htaccessFileOn, 'on');
        $this->registerTestFile('.htaccess', $htaccessFileOff, 'off');
        $this->registerTestFile('test.php', $phpOn, "on");
        $this->registerTestFile('test.php', $phpOff, "off");
    }

    /**
     *  Run the test.
     *
     *  @return TestResult   Returns a test result
     */
    public function run()
    {
        $responseOn = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/on/test.php');
        $responseOff = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/off/test.php');

        $status = null;
        $info = '';

        if ($responseOn->body == '') {
            // empty body means the HTTP request failed,
            // which we generally treat as inconclusive

            $status = null;
            $info = 'The test request failed with status code: ' . $responseOn->statusCode .
                '. We interpret this as an inconclusive result';

            // As we don't expect any of the directives to be forbidden in our .htaccess,
            // We also treat 500 as an inconclusive result
        } else {
            if (($responseOn->body == '1') && ($responseOff->body == '1')) {
                $status = true;
            } else {
                $status = false;
            }
        }

        return new TestResult($status, $info);
    }
}
