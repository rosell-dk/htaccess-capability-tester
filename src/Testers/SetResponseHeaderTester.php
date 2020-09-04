<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if Header works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class SetResponseHeaderTester extends CustomTester
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
<IfModule mod_headers.c>
    Header set X-Response-Header-Test: test
</IfModule>
EOD;

        $definitions = [
            'subdir' => 'set-response-header-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['dummy.txt', "they needed someone, so here i am"],
            ],
            'runner' => [
                [
                    'request' => 'dummy.txt',
                    'interpretation' => [
                        ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', 'test'],
                        ['failure', 'statusCode', 'equals', '500'],
                    ]
                ]
            ]
        ];

        parent::__construct($baseDir, $baseUrl, $definitions);
    }
}
