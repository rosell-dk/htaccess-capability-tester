<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HtaccessCapabilityTester;
use \HtaccessCapabilityTester\HTTPRequesterInterface;
use \HtaccessCapabilityTester\HTTPResponse;
use \HtaccessCapabilityTester\SimpleHttpRequester;
use \HtaccessCapabilityTester\TestResult;
use \HtaccessCapabilityTester\Testers\Helpers\Interpreter;

class CustomTester extends AbstractTester
{
    use TraitTestFileCreator;

    /** @var array  All tests in one place, thanks */
    protected $tests;

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     * @param  array   $tests    A single test or an array of test definitions
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl, $tests)
    {
        /*

        [
            [
                'subdir' => 'server-signature',
                'files' => [
                    ['.htaccess', $htaccessFile],
                    ['0.txt', "0"],
                ],
                'request' => '0.txt',
                'interpretation' => [
                    ['success', 'body', 'equals', '1'],
                ]
            ]
        ]
        */

        if (isset($tests[0])) {
            $this->tests = $tests;
        } else {
            $this->tests = [$tests];
        }
        parent::__construct($baseDir, $baseUrl);
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    protected function registerTestFiles()
    {
        foreach ($this->tests as $test) {
            $subDir = $test['subdir'];
            if (isset($test['files'])) {
                foreach ($test['files'] as $entry) {
                    list($filename, $content) = $entry;
                    $this->registerTestFile($subDir . '/' . $filename, $content);
                }
            }
        }
    }

    public function getSubDir()
    {
        return '';
        //return $this->tests[0]['subdir'];
    }

    public function getCacheKey()
    {
        //return '';
        return $this->tests[0]['subdir'];
    }

    /**
     *  Run
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {
        $result = null;
        foreach ($this->tests as $i => $test) {
            if (isset($test['requirements'])) {
                $hct = new HtaccessCapabilityTester($this->baseDir, $this->baseUrl);

                foreach ($test['requirements'] as $requirement) {
                    $requirementResult = $hct->callMethod($requirement);
                    if (!$requirementResult) {
                        // Skip test
                        continue 2;
                    }
                }
            }
            $url = $this->baseUrl . '/' . $test['subdir'] . '/';
            $response = $this->makeHTTPRequest($url . $test['request']);
            $result = Interpreter::interpret($response, $test['interpretation']);
            if ($result->info != 'no-match') {
                return $result;
            }
        }
        if (is_null($result)) {
            $result = new TestResult(null, 'Nothing to test!');
        }
        return $result;
    }
}
