<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if ServerSignature works
 *
 * Testing the ServerSignature directive is of interest because the directive is a core feature.
 * If a core feature doesn't work, well, it it would seem that .htaccess files are disabled completely.
 * The test is thus special. If it returns "failure" it is highly probable that the .htaccess file has
 * not been read.
 *
 * Unfortunately, the test requires PHP to examine if a server variable has been set. So the test is not
 * unlikely to come out inconclusive due to a 403 Forbidden.
 *
 * Note that the test assumes that the ServerSignature directive has not been disallowed even though
 * it is technically possible to do so by setting *AllowOverride* to *None* and by setting *AllowOverrideList*
 * to a list that does not include *ServerSignature*.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class ServerSignatureTester extends CustomTester
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
        $phpOn = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 1;
} else {
    echo 0;
}
EOD;

        $phpOff = <<<'EOD'
<?php
if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
    echo 0;
} else {
    echo 1;
}
EOD;

        $test = [
            'subdir' => 'htaccess-enabled',
            'subtests' => [
                [
                    'subdir' => 'on',
                    'files' => [
                        ['.htaccess', 'ServerSignature On'],
                        ['test.php', $phpOn],
                    ],
                    'request' => 'test.php',
                    'interpretation' => [
                        ['inconclusive', 'status-code', 'equals', '403'],
                        ['inconclusive', 'body', 'isEmpty'],
                        ['inconclusive', 'status-code', 'not-equals', '200'],
                        ['failure', 'body', 'equals', '0'],
                    ],
                    [
                        'subdir' => 'off',
                        'files' => [
                            ['.htaccess', 'ServerSignature Off'],
                            ['test.php', $phpOff],
                        ],
                        'request' => 'test.php',
                        'interpretation' => [
                            ['inconclusive', 'body', 'isEmpty'],
                            ['success', 'body', 'equals', '1'],
                            ['failure', 'body', 'equals', '0'],
                        ]
                    ]
                ]
            ]
        ];

        parent::__construct($baseDir, $baseUrl, $test);
    }
}
