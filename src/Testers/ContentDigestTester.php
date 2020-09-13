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
     * @return void
     */
    public function __construct()
    {
        $htaccessFile = <<<'EOD'
<IfModule mod_dir.c>
    DirectoryIndex index2.html
</IfModule>
EOD;

        $test = [
            'subdir' => 'content-digest',
            'subtests' => [
                [
                    'subdir' => 'on',
                    'files' => [
                        ['.htaccess', 'ContentDigest On'],
                        ['request-me.txt', 'hi'],
                    ],
                    'request' => 'request-me.txt',
                    'interpretation' => [
                        ['failure', 'status-code', 'equals', '500'],
                        ['handle-errors'],      // calls the whole thing off in case of error
                        ['failure', 'headers', 'not-contains-key', 'Content-MD5'],
                    ]
                ],
                [
                    'subdir' => 'off',
                    'files' => [
                        ['.htaccess', 'ContentDigest Off'],
                        ['request-me.txt', "hi"],
                    ],
                    'request' => 'request-me.txt',
                    'interpretation' => [
                        ['failure', 'status-code', 'equals', '500'],
                        ['failure', 'headers', 'contains-key', 'Content-MD5'],
                        ['inconclusive', 'status-code', 'not-equals', '200'],
                        ['success', 'status-code', 'equals', '200'],
                    ]
                ]
            ]
        ];

        parent::__construct($test);
    }
}
