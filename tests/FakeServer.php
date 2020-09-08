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
    public $files;

    private $config;

    public function lineUp($files)
    {
        $this->files = $files;
        //$m = new SetRequestHeaderTester();
        //$m->putFiles('');
        //print_r($files);
    }

    public function makeHTTPRequest($url)
    {
        $body = '';
        $statusCode = '200';
        $headers = [];

        switch ($this->config) {
            case 'htaccessDisabled':
                // TODO: Return the file in the files array
                //
                break;
            case 'directivesForbiddenAndFatal' :
                return new HttpResponse('', '500', []);
            case 'accessDenied':
                // TODO: what body?
                return new HttpResponse('', '403', []);
        }
        return new HttpResponse($body, $statusCode, $headers);
    }

    public function config($config)
    {
        $this->config = $config;
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
