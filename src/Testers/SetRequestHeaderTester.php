<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if RequestHeader works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class SetRequestHeaderTester extends CustomTester
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
	# Certain hosts seem to strip non-standard request headers,
	# so we use a standard one to avoid a false negative
    RequestHeader set User-Agent "request-header-test"
</IfModule>
EOD;

        $phpFile = <<<'EOD'
<?php
if (isset($_SERVER['HTTP_USER_AGENT'])) {
echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? 1 : 0;
} else {
echo 0;
}
EOD;

        $definitions = [
            'subdir' => 'set-request-header-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['test.php', $phpFile],
            ],
            'tests' => [
                [
                    'request' => 'test.php',
                    'interpretation' => [
                        ['success', 'body', 'equals', '1'],
                        ['failure', 'body', 'equals', '0'],
                        ['failure', 'statusCode', 'equals', '500'],
                    ]
                ]
            ]
        ];

        parent::__construct($baseDir, $baseUrl, $definitions);
    }
}
