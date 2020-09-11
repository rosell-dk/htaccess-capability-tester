<?php

namespace HtaccessCapabilityTester;

use \HtaccessCapabilityTester\Testers\AbstractTester;
use \HtaccessCapabilityTester\Testers\AddTypeTester;

use \HtaccessCapabilityTester\Testers\ContentDigestTester;
use \HtaccessCapabilityTester\Testers\CrashTester;
use \HtaccessCapabilityTester\Testers\DirectoryIndexTester;
use \HtaccessCapabilityTester\Testers\HtaccessEnabledTester;
use \HtaccessCapabilityTester\Testers\CustomTester;
use \HtaccessCapabilityTester\Testers\ModLoadedTester;
use \HtaccessCapabilityTester\Testers\PassInfoFromRewriteToScriptThroughRequestHeaderTester;
use \HtaccessCapabilityTester\Testers\PassInfoFromRewriteToScriptThroughEnvTester;
use \HtaccessCapabilityTester\Testers\RewriteTester;
use \HtaccessCapabilityTester\Testers\ServerSignatureTester;
use \HtaccessCapabilityTester\Testers\SetRequestHeaderTester;
use \HtaccessCapabilityTester\Testers\SetResponseHeaderTester;

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

    /** @var HttpRequesterInterface  The object used to make the HTTP request */
    private $requester;

    /** @var TestFilesLineUpperInterface  The object used to line up the test files */
    private $testFilesLineUpper;

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
        if (isset($this->requester)) {
            $tester->setHTTPRequester($this->requester);
        }
        if (isset($this->testFilesLineUpper)) {
            $tester->setTestFilesLineUpper($this->testFilesLineUpper);
        }

        $cacheKeys = [$this->baseDir, $tester->getCacheKey()];
        if (TestResultCache::isCached($cacheKeys)) {
            $testResult = TestResultCache::getCached($cacheKeys);
        } else {
            $testResult = $tester->run($this->baseDir, $this->baseUrl);
            TestResultCache::cache($cacheKeys, $testResult);
        }

        $this->infoFromLastTest = $testResult->info;
        return $testResult->status;
    }

    /**
     * Run a test, store the info and return the status.
     *
     * @param  HttpRequesterInterface  $requester
     *
     * @return void
     */
    public function setHttpRequester($requester)
    {
        $this->requester = $requester;
    }

    /**
     * Set object responsible for lining up the test files.
     *
     * @param  TestFilesLineUpperInterface  $testFilesLineUpper
     * @return void
     */
    public function setTestFilesLineUpper($testFilesLineUpper)
    {
        $this->testFilesLineUpper = $testFilesLineUpper;
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
        return $this->runTest(new HtaccessEnabledTester());
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
        return $this->runTest(new ModLoadedTester($moduleName));
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
    public function doesRewritingWork()
    {
        return $this->runTest(new RewriteTester());
    }

    /**
     * Test if AddType works.
     *
     * The .htaccess in this test uses the following directives:
     * - IfModule (core)
     * - AddType  (mod_mime, FileInfo)
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesAddTypeWork()
    {
        return $this->runTest(new AddTypeTester());
    }

    /**
     * Test if setting a Response Header with the Header directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesSetResponseHeaderWork()
    {
        return $this->runTest(new SetResponseHeaderTester());
    }

    /**
     * Test if setting a Request Header with the RequestHeader directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesSetRequestHeaderWork()
    {
        return $this->runTest(new SetRequestHeaderTester());
    }

    /**
     * Test if ContentDigest directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesContentDigestSetWork()
    {
        return $this->runTest(new ContentDigestTester());
    }

    /**
     * Test if ServerSignature directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesServerSignatureWork()
    {
        return $this->runTest(new ServerSignatureTester());
    }


    /**
     * Test if DirectoryIndex works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesDirectoryIndexWork()
    {
        return $this->runTest(new DirectoryIndexTester());
    }

    /**
     * Test a complex construct for passing information from a rewrite to a script through a request header.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesPassingInfoFromRewriteToScriptThroughRequestHeaderWork()
    {
        return $this->runTest(new PassInfoFromRewriteToScriptThroughRequestHeaderTester());
    }


    /**
     * Test if an environment variable can be set in a rewrite rule  and received in PHP.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function doesPassingInfoFromRewriteToScriptThroughEnvWorkTester()
    {
        return $this->runTest(new PassInfoFromRewriteToScriptThroughEnvTester());
    }

    /**
     * Call one of the methods of this class (not all allowed).
     *
     * @param string  $functionCall  ie "doesRewritingWork()"
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function callMethod($functionCall)
    {
        switch ($functionCall) {
            case 'htaccessEnabled()':
                return $this->htaccessEnabled();
            case 'doesRewritingWork()':
                return $this->doesRewritingWork();
            case 'doesAddTypeWork()':
                return $this->doesAddTypeWork();
            case 'doesSetResponseHeaderWork()':
                return $this->doesSetResponseHeaderWork();
            case 'doesSetRequestHeaderWork()':
                return $this->doesSetRequestHeaderWork();
            case 'doesContentDigestSetWork()':
                return $this->doesContentDigestSetWork();
            case 'doesDirectoryIndexWork()':
                return $this->doesDirectoryIndexWork();
            case 'doesPassingInfoFromRewriteToScriptThroughRequestHeaderWork()':
                return $this->doesPassingInfoFromRewriteToScriptThroughRequestHeaderWork();
            case 'doesPassingInfoFromRewriteToScriptThroughEnvWorkTester()':
                return $this->doesPassingInfoFromRewriteToScriptThroughEnvWorkTester();
            default:
                throw new \Exception('The method is not callable');
        }

        // TODO:             moduleLoaded($moduleName)
    }

    /**
     * Crash-test some .htaccess rules.
     *
     * Tests if the server can withstand the given rules without going fatal.
     *
     * - success: if the rules does not result in status 500.
     * - failure: if the rules results in status 500 while a request to a file in a directory
     *        without any .htaccess succeeds (<> 500)
     * - inconclusive: if the rules results in status 500 while a request to a file in a directory
     *        without any .htaccess also fails (500)
     *
     * @param string       $rules   Rules to crash-test
     * @param string       $subDir  (optional) Subdir for the .htaccess to reside.
     *                              if left out, a unique string will be generated
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function crashTest($rules, $subDir = null)
    {
        return $this->runTest(new CrashTester($rules, $subDir));
    }

    /**
     * Crash-test an innocent request.
     *
     * Confirm that an innocent request does not results in a 500.
     * The "innocent request" is a request to a "request-me.txt" file in a directory that does not contain a .htaccess.
     *
     * - success: if the request does not result in status 500.
     * - failure: if the request results in status 500
     * - inconclusive: if the request fails (ie timeout)
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function crashTestInnocentRequest()
    {
        // TODO:
        // We are interested in other status codes. For example a 404 probably means that the URL supplied was wrong.
        // 403 is also interesting.
        // But we do not want to make separate tests for each. So make sure the innocent request is only made once and
        // the status is cached.
        return true;
    }

    /**
     * Run a custom test.
     *
     * @param array       $definition
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function customTest($definition)
    {
        return $this->runTest(new CustomTester($definition));
    }
}
