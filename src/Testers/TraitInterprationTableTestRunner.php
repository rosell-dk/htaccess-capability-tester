<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HTTPResponse;
use \HtaccessCapabilityTester\TestResult;
use \HtaccessCapabilityTester\Testers\Helpers\Interpreter;

/**
 * Trait for running tests that are defined with an interpretation map
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
trait TraitInterprationTableTestRunner
{

    /**
     * Define the "file" to request.
     *
     * @return string  The last part of the url path for the request
     */
    abstract protected function requestFile();

    /**
     * Define the table used for interpreting the result.
     *
     * @return array A table defining how to interpret the response
     */
    abstract protected function interpretationTable();

    /**
     *  Run
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {

        $url = $this->baseUrl . '/' . $this->subDir . '/' . $this->requestFile();
        $response = $this->makeHTTPRequest($url);

        return Interpreter::interpret($response, $this->interpretationTable());
    }
}
