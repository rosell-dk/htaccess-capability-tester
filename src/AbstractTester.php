<?php

namespace HtaccessCapabilityTester;

abstract class AbstractTester
{
    use TraitTestFileCreator;


    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    /** @var string  A subdir */
    protected $subDir;

    /** @var array  Test files for the test */
    protected $testFiles;

    /** @var iHTTPRequestor  An object for making the HTTP request */
    protected $httpRequestor;

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

    protected function registerTestFile($fileName, $content, $subDir = '') {
        $this->testFiles[] = [$fileName, $content, $subDir];
    }

    abstract protected function runTest();

    public function __construct($baseDir2, $baseUrl2) {
        $this->baseDir = $baseDir2;
        $this->baseUrl = $baseUrl2;
        $this->subDir = $this->getSubDir();
        $this->registerTestFiles();
        $this->createTestFilesIfNeeded();
    }

    /**
     * Make a HTTP request to a URL.
     *
     * @return  string  The response text
     */
    protected function makeHTTPRequest($url) {
        if (!isset($this->httpRequestor)) {
            $this->httpRequestor = new SimpleHttpRequestor();
        }
        return $this->httpRequestor->makeHTTPRequest($url);
    }

    protected function setHTTPRequestor($httpRequestor) {
        $this->httpRequestor = $httpRequestor;
    }

/*
    protected function runStandardTest() {
    }*/


}
