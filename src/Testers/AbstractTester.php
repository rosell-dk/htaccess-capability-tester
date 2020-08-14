<?php

namespace HtaccessCapabilityTester\Testers;

abstract class AbstractTester
{
    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    /** @var string  A subdir */
    protected $subDir;

    abstract protected function createTestFiles();
    abstract protected function runTest();

    public function __construct($baseDir2, $baseUrl2, $subDir = '') {
        $this->baseDir = $baseDir2;
        $this->baseUrl = $baseUrl2;
        $this->subDir = $subDir;
    }

    protected function putFile($fileName, $content, $subSubDir = '') {
        $dir = $this->baseDir . '/' . $this->subDir;
        if ($subSubDir != '') {
            $dir .= '/' . $subSubDir;
        }
        $path = $dir . '/' . $fileName;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return file_put_contents($path, $content);
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
