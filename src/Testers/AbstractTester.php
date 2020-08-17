<?php

namespace HtaccessCapabilityTester\Testers;

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

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    abstract protected function registerTestFiles();

    protected function registerTestFile($fileName, $content, $subDir = '') {
        $this->testFiles[] = [$fileName, $content, $subDir];
    }

    abstract protected function runTest();

    public function __construct($baseDir2, $baseUrl2, $subDir = '') {
        $this->baseDir = $baseDir2;
        $this->baseUrl = $baseUrl2;
        $this->subDir = $subDir;
        $this->registerTestFiles();
        $this->createTestFilesIfNeeded();
    }

    protected function makeHTTPRequest($url) {
        $text = file_get_contents($url);
        // var_dump($http_response_header);
        return $text;
    }
/*
    protected function runStandardTest() {
    }*/


}
