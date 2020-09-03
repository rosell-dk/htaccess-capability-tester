<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if setting DirectoryIndex works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class DirectoryIndexTester extends AbstractTester
{

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'directory-index-tester';
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles()
    {

        $htaccess = <<<'EOD'
<IfModule mod_dir.c>
    DirectoryIndex index2.html
</IfModule>
EOD;

        $this->registerTestFile('.htaccess', $htaccess);
        $this->registerTestFile('index.html', "0");
        $this->registerTestFile('index2.html', "1");
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
        $response = $this->makeHTTPRequest($dir . '/');

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
                if ($response->body == '1') {
                    $status = true;
                } elseif ($response->body == '0') {
                    $status = false;
                } else {
                    $info = 'unexpected response: ' . $response->body;
                }
            }
        }

        return new TestResult($status, $info);
    }
}
