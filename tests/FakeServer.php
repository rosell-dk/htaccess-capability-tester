<?php

namespace HtaccessCapabilityTester\Tests;

use HtaccessCapabilityTester\HttpResponse;
use HtaccessCapabilityTester\HttpRequesterInterface;
use HtaccessCapabilityTester\TestFilesLineUpperInterface;
use HtaccessCapabilityTester\TestResult;
use HtaccessCapabilityTester\Testers\AbstractTester;

class FakeServer implements TestFilesLineUpperInterface, HttpRequesterInterface
{

    /** @var array  Files on the server */
    private $files;

    /** @var array  Files as a map, by filename */
    private $filesMap;

    /** @var bool  If .htaccess processing is disabled */
    private $htaccessDisabled = false;

    /** @var bool  If all directives should be disallowed (but .htaccess still read) */
    private $disallowAllDirectives = false;

    /** @var bool  If server should go fatal about forbidden directives */
    private $fatal = false;

    /** @var bool  If access is denied for all requests */
    private $accessAllDenied = false;

    /** @var array  Predefined responses for certain urls */
    private $responses;


    public function lineUp($files)
    {
        $this->files = $files;
        $this->filesMap = [];
        foreach ($files as $i => list($filename, $content)) {
            $this->filesMap[$filename] = $content;
        }
        //$m = new SetRequestHeaderTester();
        //$m->putFiles('');
        //print_r($files);
    }

    public function makeHTTPRequest($url)
    {
        $body = '';
        $statusCode = '200';
        $headers = [];

        if (isset($this->responses[$url])) {
            //echo 'predefined: ' . $url;
            return $this->responses[$url];
        }

        $simplyServeRequested = ($this->htaccessDisabled || ($this->disallowAllDirectives && (!$this->fatal)));
        if ($simplyServeRequested) {
            // Simply return the file that was requested
            if (isset($this->filesMap[$url])) {
                return new HttpResponse($this->filesMap[$url], '200', []); ;
            }
        }
        if (($this->disallowAllDirectives) && ($this->fatal)) {

            $urlToHtaccessInSameFolder = dirname($url) . '/.htaccess';
            $doesFolderContainHtaccess = isset($this->filesMap[$urlToHtaccessInSameFolder]);

            if ($doesFolderContainHtaccess) {
                return new HttpResponse('', '500', []); ;
            }
        }

        if ($this->accessAllDenied) {
            // TODO: what body?
            return new HttpResponse('', '403', []);
        }

        return new HttpResponse('Not found', '404', []);
    }

    /**
     * Disallows all directives, but do still process .htaccess.
     *
     * In essence: Fail, if the folder contains an .htaccess file
     *
     * @param string $fatal  fatal|nonfatal
     */
    public function disallowAllDirectives($fatal)
    {
        $this->disallowAllDirectives = true;
        $this->fatal = ($fatal = 'fatal');
    }

    public function disableHtaccess()
    {
        $this->htaccessDisabled = true;
    }

    public function denyAllAccess()
    {
        $this->accessAllDenied = true;
    }

    // TODO: denyAccessToPHP

    /**
     * @param array $responses
     */
    public function setResponses($responses)
    {
        $this->responses = $responses;
    }

    /**
     * @param  AbstractTester $tester
     * @return TestResult
     */
    public function runTester($tester)
    {
        $tester->setTestFilesLineUpper($this);
        $tester->setHttpRequester($this);

        return $tester->run('', '');
    }
}
