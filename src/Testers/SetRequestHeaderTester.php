<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if setting request headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class SetRequestHeaderTester extends AbstractTester
{

    use TraitStandardTestRunner;

    public function __construct($baseDir2, $baseUrl2)
    {
        parent::__construct($baseDir2, $baseUrl2, 'set-request-header-tester');
    }

    /**
     * Register the test files using the "registerTestFile" method
     *
     * @return  void
     */
    public function registerTestFiles() {

$file = <<<'EOD'
<?php
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? 1 : 0;
} else {
    echo 0;
}
EOD;
        $this->registerTestFile('test.php', $file);

$file = <<<'EOD'
<IfModule mod_headers.c>
	# Certain hosts seem to strip non-standard request headers,
	# so we use a standard one to avoid a false negative
    RequestHeader set User-Agent "request-header-test"
</IfModule>
EOD;

        $this->registerTestFile('.htaccess', $file);
    }
}
