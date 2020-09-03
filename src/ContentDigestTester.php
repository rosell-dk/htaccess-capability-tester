<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if setting response headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class ContentDigestTester extends AbstractTester
{

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'content-digest-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {

        $this->registerTestFile('.htaccess', 'ContentDigest On', 'on');
        $this->registerTestFile('.htaccess', 'ContentDigest Off', 'off');

        // Just to have something to request
        $this->registerTestFile('dummy.txt', "they needed someone, so here i am", 'on');
        $this->registerTestFile('dummy.txt', "they needed someone, so here i am", 'off');
    }

    /**
     *  Run the tets
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {
        $status = null;
        $info = '';

        $dir = $this->baseUrl . '/' . $this->subDir;
        $response = $this->makeHTTPRequest($dir . '/on/dummy.txt');

        if ($response->statusCode == '500') {
            // A 500 Internal Server error is interpreted as meaning that the .htaccess contains
            // a forbidden directive
            $status = false;
            $info = 'The test request responded with a 500 Internal Server Error. ' .
                'It probably means that the Header directive is forbidden';
        } else {
            if ($response->statusCode !== '200') {
                $status = null;
                $info = 'The test request failed with status code: ' . $response->statusCode .
                    '. We interpret this as an inconclusive result.';
            } else {
                $headersHash = $response->getHeadersHash();
                if (!isset($headersHash['Content-MD5'])) {
                    $status = false;
                } else {
                    $responseOff = $this->makeHTTPRequest($dir . '/off/dummy.txt');
                    $headersHashOff = $responseOff->getHeadersHash();

                    if (isset($headersHashOff['Content-MD5'])) {
                        $status = false;
                    } else {
                        $status = true;
                    }
                }
            }
        }

        return new TestResult($status, $info);
    }
}
