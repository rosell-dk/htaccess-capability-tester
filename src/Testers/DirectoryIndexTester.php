<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if setting DirectoryIndex works
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class DirectoryIndexTester extends CustomTester
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
            'subdir' => 'directory-index-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['index.html', "0"],
                ['index2.html', "1"]
            ],
            'runner' => [
                [
                    'request' => '',    // We request the index, that is why its empty
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
