<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\HTTPRequesterInterface;
use \HtaccessCapabilityTester\HTTPResponse;
use \HtaccessCapabilityTester\SimpleHttpRequester;
use \HtaccessCapabilityTester\TestResult;
use \HtaccessCapabilityTester\Testers\Helpers\Interpreter;

class CustomTester extends AbstractTester
{
    use TraitTestFileCreator;

    /** @var array  All definitions in one place, thanks */
    protected $definitions;

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl, $definitions)
    {
        $this->definitions = $definitions;
        parent::__construct($baseDir, $baseUrl);
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    protected function registerTestFiles()
    {
        foreach ($this->definitions['files'] as $entry) {
            list($filename, $content) = $entry;
            $this->registerTestFile($filename, $content);
        }
    }

    public function getSubDir()
    {
        return $this->definitions['subdir'];
    }

    /**
     *  Run
     *
     *  @return TestResult   Returns a test result
     *  @throws \Exception  In case the test cannot be run due to serious issues
     */
    public function run()
    {
        /*
        ie:

        'runner' => [
            [
                'request' => '0.txt',
                'interpretation' => [
                    ['success', 'body', 'equals', '1'],
                    ['failure', 'body', 'equals', '0'],
                    ['failure', 'statusCode', 'equals', '500'],
                ]
            ]
        ]
        */
        $runner = $this->definitions['runner'];
        $result = null;
        foreach ($runner as $i => $runEntry) {
            $url = $this->baseUrl . '/' . $this->subDir . '/';
            $response = $this->makeHTTPRequest($url . $runEntry['request']);
            $result = Interpreter::interpret($response, $runEntry['interpretation']);
            if (!is_null($result->status)) {
                return $result;
            }
        }
        if (count($runner) == 1) {
            return $result;
        } else {
            return new TestResult(null, 'All tests where inconclusive.');
        }
    }
}
