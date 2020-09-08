<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if an environment variable can be passed through RequestHeader and received with PHP.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class PassEnvThroughRequestHeaderTester extends CustomTester
{

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $htaccessFile = <<<'EOD'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Testing if we can pass an environment variable through a request header
    # We pass document root, because that can easily be checked by the script

    <IfModule mod_headers.c>
      RequestHeader set PASSTHROUGHHEADER "%{PASSTHROUGHHEADER}e" env=PASSTHROUGHHEADER
    </IfModule>
    RewriteRule ^test\.php$ - [E=PASSTHROUGHHEADER:%{DOCUMENT_ROOT},L]

</IfModule>
EOD;

        $phpFile = <<<'EOD'
<?php
if (isset($_SERVER['HTTP_PASSTHROUGHHEADER'])) {
    echo ($_SERVER['HTTP_PASSTHROUGHHEADER'] == $_SERVER['DOCUMENT_ROOT'] ? 1 : 0);
    exit;
}
echo '0';
EOD;

        $test = [
            'subdir' => 'pass-env-through-request-header-tester',
            'files' => [
                ['.htaccess', $htaccessFile],
                ['test.php', $phpFile],
            ],
            'request' => 'test.php',
            'interpretation' => [
                ['success', 'body', 'equals', '1'],
                ['failure', 'body', 'equals', '0'],
                ['failure', 'status-code', 'equals', '500'],
            ]
        ];

        parent::__construct($test);
    }
}
