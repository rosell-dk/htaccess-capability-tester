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
     * @return void
     */
    public function __construct()
    {
        $htaccessFile = <<<'EOD'
<IfModule mod_headers.c>
    Header set X-Response-Header-Test: test
</IfModule>
EOD;

        $test = [
            'subdir' => 'set-response-header-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['request-me.txt', "hi"],
            ],
            'request' => 'request-me.txt',
            'interpretation' => [
                ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', 'test'],
                ['failure', 'status-code', 'equals', '500'],
                ['inconclusive', 'status-code', 'not-equals', '200'],
                ['failure'],
            ]
        ];

        parent::__construct($test);
    }
}
