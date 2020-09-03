<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if setting response headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class SetResponseHeaderTester extends AbstractTester
{

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'set-response-header-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {

        $file = <<<'EOD'
<IfModule mod_headers.c>
    Header set X-Response-Header-Test: test
</IfModule>
EOD;

        $this->registerTestFile('.htaccess', $file);

        // Just to have something to request
        $this->registerTestFile('dummy.txt', "they needed someone, so here i am");
    }

    /**
     *  Run the tets
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {
        $response = $this->makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/dummy.txt');

        $status = null;
        $info = '';

        if (in_array('X-Response-Header-Test: test', $response->headers)) {
            $status = true;
        } else {
            if ($response->statusCode == '500') {
                // A 500 Internal Server error is interpreted as meaning that the .htaccess contains
                // a forbidden directive
                $status = false;
                $info = 'The test request responded with a 500 Internal Server Error. ' .
                    'It probably means that the Header directive is forbidden';
            } elseif ($response->statusCode !== '200') {
                $status = null;
                $info = 'The test request failed with status code: ' . $response->statusCode .
                    '. We interpret this as an inconclusive result.';
            } else {
                $status = false;
            }
        }
//
        //echo '<pre>' . print_r($response->headers, true) . '</pre>';

        return new TestResult($status, $info);
    }
}
