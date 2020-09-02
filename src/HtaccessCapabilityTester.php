<?php

namespace HtaccessCapabilityTester;

/**
 * Main entrance.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class HtaccessCapabilityTester
{

    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    /** @var string  Additional info regarding last test (often empty) */
    public $infoFromLastTest;

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl)
    {
        $this->baseDir = $baseDir;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Run a test, store the info and return the status.
     *
     * @param  AbstractTester  $tester
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    private function runTest($tester)
    {
        $testResult = $tester->run();
        $this->infoFromLastTest = $testResult->info;
        return $testResult->status;
    }

    /**
     * Test if .htaccess files are enabled
     *
     * Apache can be configured to completely ignore .htaccess files. This test examines
     * if .htaccess files are proccesed.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function htaccessEnabled()
    {
        return $this->runTest(new HtaccessEnabledTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if a module is loaded.
     *
     * This test detects if directives inside a "IfModule" is run for a given module
     *
     * @param string       $moduleName  A valid Apache module name (ie "rewrite")
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function moduleLoaded($moduleName)
    {
        return $this->runTest(new ModLoadedTester($this->baseDir, $this->baseUrl, $moduleName));
    }

    /**
     * Test if rewriting works.
     *
     * The .htaccess in this test uses the following directives:
     * - IfModule
     * - RewriteEngine
     * - Rewrite
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canRewrite()
    {
        return $this->runTest(new RewriteTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if setting a Request Header with the RequestHeader directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canSetRequestHeader()
    {
        return $this->runTest(new SetRequestHeaderTester($this->baseDir, $this->baseUrl));
    }
}
