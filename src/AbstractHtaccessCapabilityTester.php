<?php

namespace HtaccessCapabilityTester;

abstract class AbstractHtaccessCapabilityTester
{
    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    final public function __construct($baseDir2, $baseUrl2) {
        $this->baseDir = $baseDir2;
        $this->baseUrl = $baseUrl2;
    }

    protected function putFile($subdir, $fileName, $content) {
        $dir = $this->baseDir . '/' . $subdir;
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
