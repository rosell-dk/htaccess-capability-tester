<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HtaccessCapabilityTester;
use \HtaccessCapabilityTester\TestResult;

/**
 * Class for testing if .htaccess files are processed
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

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {
        // No test files for this test
    }

    /**
     *  Run the test.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @return TestResult   Returns a test result
     */
    public function run($baseDir, $baseUrl)
    {
        $this->prepareForRun($baseDir, $baseUrl);

        $status = null;
        $info = '';
        $hct = new HtaccessCapabilityTester($baseDir, $baseUrl);

        // If we can find anything that works, well the .htaccess must have been proccesed!
        if ($hct->canSetServerSignature()    // Override: None,  Status: Core, REQUIRES PHP
            || $hct->canContentDigest()      // Override: Options,  Status: Core
            || $hct->canAddType()            // Override: FileInfo, Status: Base, Module: mime
            || $hct->canSetDirectoryIndex()  // Override: Indexes,  Status: Base, Module: mod_dir
            || $hct->canRewrite()            // Override: FileInfo, Status: Extension, Module: rewrite
            || $hct->canSetResponseHeader()  // Override: FileInfo, Status: Extension, Module: headers
        ) {
            $status = true;
        } else {
            // The canSetServerSignature() test is special because if it comes out as a failure,
            // we can be *almost* certain that the .htaccess has been completely disabled

            $canSetServerSignature = $hct->canSetServerSignature();
            if ($canSetServerSignature === false) {
                $status = false;
                $info = 'ServerSignature directive does not work - and it is in core';
            } else {
                // Last bullet in the gun:
                // Try an .htaccess with syntax errors in it.
                // (we do this lastly because it may generate an entry in the error log)
                $crash = ($hct->crashTest('aoeu', 'htaccess-enabled-tester/crash-test') === false);
                if ($crash) {
                    $status = true;
                    $info = 'syntax error in an .htaccess causes crash';
                } else {
                    $status = false;
                    $info = 'syntax error in an .htaccess does not cause crash';
                }
            }
        }
        return new TestResult($status, $info);
    }
}
