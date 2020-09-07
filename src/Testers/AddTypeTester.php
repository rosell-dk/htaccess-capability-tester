<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if AddType works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class AddTypeTester extends CustomTester
{

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl)
    {
        $htaccessFile = <<<'EOD'
<IfModule mod_mime.c>
    AddType image/gif .test
</IfModule>
EOD;

        $test = [
            'subdir' => 'add-type-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['dummy.test', "they needed someone, so here i am"],
            ],
            'request' => 'dummy.test',
            'interpretation' => [
                ['success', 'headers', 'contains-key-value', 'Content-Type', 'image/gif'],
                ['failure', 'status-code', 'equals', '500'],
                ['failure', 'status-code', 'equals', '200']
            ]
        ];

        parent::__construct($baseDir, $baseUrl, $test);
    }
}
