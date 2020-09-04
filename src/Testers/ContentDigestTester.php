<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if setting ContentDigest works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class ContentDigestTester extends CustomTester
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
<IfModule mod_dir.c>
    DirectoryIndex index2.html
</IfModule>
EOD;

        $definitions = [
            'subdir' => 'content-digest-tester',
            'files' => [
                ['on/.htaccess', 'ContentDigest On'],
                ['on/dummy.txt', ""],
                ['off/.htaccess', 'ContentDigest Off'],
                ['off/dummy.txt', ""],
            ],
            'tests' => [
                [
                    'request' => 'on/dummy.txt',
                    'interpretation' => [
                        ['failure', 'statusCode', 'equals', '500'],
                        ['inconclusive', 'statusCode', 'not-equals', '200'],        // calls the whole thing off
                        ['failure', 'headers', 'not-contains-key', 'Content-MD5'],
                    ]
                ],
                [
                    'request' => 'off/dummy.txt',
                    'interpretation' => [
                        ['failure', 'statusCode', 'equals', '500'],
                        ['failure', 'headers', 'contains-key', 'Content-MD5'],
                        ['inconclusive', 'statusCode', 'not-equals', '200'],
                        ['success', 'statusCode', 'equals', '200'],
                    ]
                ],

            ]
        ];

        parent::__construct($baseDir, $baseUrl, $definitions);
    }
}
