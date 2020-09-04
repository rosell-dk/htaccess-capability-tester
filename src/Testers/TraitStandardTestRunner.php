<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HTTPResponse;
use \HtaccessCapabilityTester\TestResult;

/**
 * Trait for running standard tests
 *
 * A standard tester contains a "test.php" file, which outputs one of the following:
 * - "1" if the feature works
 * - "0" if the feature does not work
 * - A failure message if it could not be established if the feature works or not
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
trait TraitStandardTestRunner
{

    /**
     *  Run a standard test (one, which contains a test.php file, which outputs either "0", "1"
     *  or a failure message (in case the test is inconclusive)
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {
        // TODO:
        // We need response code as well.
        // 500 => false
        // Other errors => Throw exception

        $response = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/test.php');

        $status = null;
        $info = '';

        if ($response->body == '') {
            // empty body means the HTTP request failed,
            // which we generally treat as inconclusive
            $status = null;
            $info = 'The test request failed with status code: ' . $response->statusCode .
                '. We interpret this as an inconclusive result.';
        } else {
            switch ($response->body) {
                case '1':
                    $status = true;
                    break;
                case '0':
                    $status = false;
                    break;
                default:
                    // text in the body means the test was inconclusive
                    $status = null;
                    $info = $response->body;
            }
        }

        if ($response->statusCode == '500') {
            // A 500 Internal Server error is interpreted as meaning that the .htaccess contains
            // a forbidden directive
            $status = false;
            $info = 'The test request responded with a 500 Internal Server Error. ' .
                'It probably means that the .htaccess contains a forbidden directive';
        };

        return new TestResult($status, $info);
    }
}
