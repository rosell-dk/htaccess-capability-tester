<?php

namespace HtaccessCapabilityTester\Testers;

use HtaccessCapabilityTester\AbstractHtaccessCapabilityTester;

/**
 * Class for testing if setting request headers in an .htaccess file works.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class SetRequestHeaderTester extends AbstractHtaccessCapabilityTester
{

    const subdir = 'set-request-header-tester';

    /**
     * Creates the neccessary test files.
     *
     * @return  void
     */
    public function createTestFiles() {

$file = <<<'EOD'
<?php
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    echo  $_SERVER['HTTP_USER_AGENT'] == 'request-header-test' ? 1 : 0;
} else {
    echo 0;
}
EOD;
        self::putFile(self::subdir, 'test.php', $file);

$file = <<<'EOD'
<IfModule mod_headers.c>
	# Certain hosts seem to strip non-standard request headers,
	# so we use a standard one to avoid a false negative
    RequestHeader set User-Agent "request-header-test"
</IfModule>
EOD;

        self::putFile(self::subdir, '.htaccess', $file);
    }

    /**
     *  Run the test to see if a header can be successfully set using the .htaccess.
     *
     *  @return bool|null  Returns true if it can be established that it works, false if it can
     *                       be established that it does not work, or null if nothing could be
     *                       established due to some other failure
     */
    public function runTest() {
        $this->createTestFiles();

        $responseText = self::makeHTTPRequest($this->baseUrl . '/' . self::subdir . '/test.php');
        if ($responseText == '1') {
            return true;
        };
        if ($responseText == '0') {
            return false;
        };
    }
}
