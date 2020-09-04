<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HtaccessCapabilityTester;
use \HtaccessCapabilityTester\TestResult;

/*  TODO:
    Rewrite this class to extend CustomTester too!
    we can set a new "requirements" property on the tests:

    'tests' => [
        [
            'requirements' => ['canRewrite'],
            'request' => '',
            'interpretation' => [
                ['success'],
            ]
        ]
    ]

    or:

    'tests' => [
        [
            'request' => '',
            'interpretation' => [
                ['success', 'canContentDigest'],
                ['success', 'canAddType'],
                ...
            ]
        ]
    ]


*/

/**
 * Class for testing if rewriting works at the tested location.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
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

    private function registerTestFiles1()
    {
        // Test files, method 1: Using ServerSignature
        // --------------------------------------------------

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
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {
        $this->registerTestFiles1();
    }

    private function runTestUsingServerSignature()
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

    /**
     *  Run the test.
     *
     *  @return TestResult   Returns a test result
     */
    public function run()
    {

        $testResult = $this->runTestUsingServerSignature();
        $status = $testResult->status;
        $info = $testResult->info;

        if (is_null($testResult->status)) {
            $hct = new HtaccessCapabilityTester($this->baseDir, $this->baseUrl);

            // We are here because the first test was inconclusive.
            // This probably means that PHP scripts are forbidden.

            // But we have many tests around here that does not rely on PHP.
            // If any of those tests succeeds, it will mean that the .htaccess is read.

            if ($hct->canContentDigest()         // Override: Options,  Status: Core
                || $hct->canAddType()            // Override: FileInfo, Status: Base, Module: mime
                || $hct->canSetDirectoryIndex()  // Override: Indexes,  Status: Base, Module: mod_dir
                || $hct->canRewrite()            // Override: FileInfo, Status: Extension, Module: rewrite
                || $hct->canSetResponseHeader()  // Override: FileInfo, Status: Extension, Module: headers
            ) {
                $status = true;
                $info = '';
            }
        }
        return new TestResult($status, $info);
    }
}
