<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HttpRequesterInterface;
use \HtaccessCapabilityTester\HttpResponse;
use \HtaccessCapabilityTester\SimpleHttpRequester;
use \HtaccessCapabilityTester\SimpleTestFileLineUpper;
use \HtaccessCapabilityTester\TestFilesLineUpperInterface;
use \HtaccessCapabilityTester\TestResult;

abstract class AbstractTester
{
    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    /** @var string  Subdir to put .htaccess files in */
    protected $subDir;

    /** @var array  Test files for the test */
    protected $testFiles;

    /** @var HttpRequesterInterface  An object for making the HTTP request */
    protected $httpRequester;

    /** @var TestFilesLineUpperInterface  An object for lining up the test-files */
    protected $testFilesLineUpper;

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    abstract protected function registerTestFiles();

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    abstract protected function getSubDir();

    /**
     * Get key for caching purposes.
     *
     * Return a unique key. The default is to use the subdir. However, if a concrete Tester class
     * can test different things, it must override this method and make sure to return a different
     * key per thing it can test
     *
     * @return  string  A key it can be cached under
     */
    public function getCacheKey()
    {
        return $this->getSubDir();
    }

    public function getBaseDir()
    {
        return $this->baseDir;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Child classes must that implement the registerTestFiles method must call
     * this method to register each test file.
     *
     * @return  void
     */
    protected function registerTestFile($filename, $content)
    {
        $this->testFiles[] = [$this->baseDir . '/' . $filename, $content];
    }

    /**
     * Last moment preparations before running the test
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @throws \Exception  In case the test cannot be prepared due to serious issues
     */
    protected function prepareForRun($baseDir, $baseUrl)
    {
        $this->baseDir = $baseDir;
        $this->baseUrl = $baseUrl;
        $this->testFiles = [];
        $this->registerTestFiles();
        $this->lineUpTestFiles();
    }

    abstract public function run($baseDir, $baseUrl);

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subDir = $this->getSubDir();
    }

    /**
     * Make a HTTP request to a URL.
     *
     * @param  string  $url  The URL to make the HTTP request to
     *
     * @return  HttpResponse  A HttpResponse object, which simply contains body and status code.
     */
    protected function makeHTTPRequest($url)
    {
        if (!isset($this->httpRequester)) {
            $this->httpRequester = new SimpleHttpRequester();
        }
        return $this->httpRequester->makeHTTPRequest($url);
    }

    /**
     * Set HTTP requester object, which handles making HTTP requests.
     *
     * @param  HttpRequesterInterface  $httpRequester  The HTTPRequester to use
     * @return void
     */
    public function setHTTPRequester($httpRequester)
    {
        $this->httpRequester = $httpRequester;
    }

    public function lineUpTestFiles()
    {
        if (!isset($this->testFilesLineUpper)) {
            $this->testFilesLineUpper = new SimpleTestFileLineUpper();
        }
        $this->testFilesLineUpper->lineUp($this->testFiles);
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
}
