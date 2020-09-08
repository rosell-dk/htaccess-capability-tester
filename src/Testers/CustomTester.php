<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HtaccessCapabilityTester;
use \HtaccessCapabilityTester\HttpRequesterInterface;
use \HtaccessCapabilityTester\HttpResponse;
use \HtaccessCapabilityTester\SimpleHttpRequester;
use \HtaccessCapabilityTester\TestResult;
use \HtaccessCapabilityTester\Testers\Helpers\Interpreter;

class CustomTester extends AbstractTester
{
    /** @var array  A definition defining the test */
    protected $test;

    /** @var array  For convenience, all tests */
    private $tests;

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     * @param  array   $test     The test (may contain subtests)
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl, $test)
    {
        $this->test = $test;

        if (isset($test['subtests'])) {
            $this->tests = $test['subtests'];

            // Add main subdir to subdir for all subtests
            foreach ($this->tests as &$subtest) {
                if (isset($subtest['subdir'])) {
                    $subtest['subdir'] = $test['subdir'] . '/' . $subtest['subdir'];
                }
            }
        } else {
            $this->tests = [$test];
        }

        //echo '<pre>' . print_r($this->tests, true) . '</pre>';
        //echo json_encode($this->tests) . '<br>';
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
            if (isset($test['files'])) {
                foreach ($test['files'] as $file) {
                    // Two syntaxes are allowed:
                    // - Simple array (ie: ['0.txt', '0']
                    // - Named, ie:  ['filename' => '0.txt', 'content' => '0']
                    // The second makes more readable YAML definitions
                    if (isset($file['filename'])) {
                        $filename = $file['filename'];
                        $content = $file['content'];
                    } else {
                        list ($filename, $content) = $file;
                    }
                    $this->registerTestFile($test['subdir'] . '/' . $filename, $content);
                }
            }
        }
    }

    public function getSubDir()
    {
        return $this->test['subdir'];
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
            if (isset($test['request'])) {
                $requestUrl = $this->baseUrl . '/' . $test['subdir'] . '/';
                if (isset($test['request']['url'])) {
                    $requestUrl .= $test['request']['url'];
                } else {
                    $requestUrl .= $test['request'];
                }
                //echo $requestUrl . '<br>';
                $response = $this->makeHTTPRequest($requestUrl);
                $result = Interpreter::interpret($response, $test['interpretation']);
                if ($result->info != 'no-match') {
                    return $result;
                }
            }
        }
        if (is_null($result)) {
            $result = new TestResult(null, 'Nothing to test!');
        }
        return $result;
    }
}
