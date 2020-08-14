<?php

namespace HtaccessCapabilityTester\Testers;

use HtaccessCapabilityTester\AbstractHtaccessCapabilityTester;

/**
 * Class for testing if rewriting works.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class RewriteTester extends AbstractHtaccessCapabilityTester
{

    const subdir = 'rewrite-tester';

    /**
     * Creates the neccessary test files.
     *
     * @return  void
     */
    public function createTestFiles() {

        $file = <<<'EOD'
<IfModule mod_rewrite.c>

    # Testing for mod_rewrite
    # -----------------------
    # If mod_rewrite is enabled, redirect to 1.php, which returns "1".
    # If mod_rewrite is disabled, the rewriting fails, and we end at test.php, which always returns 0.

    RewriteEngine On
    RewriteRule ^test\.php$ 1.php [L]

</IfModule>
EOD;

        self::putFile(self::subdir, '.htaccess', $file);

        $file = <<<'EOD'
<?php
echo '1';
EOD;
        self::putFile(self::subdir, '1.php', $file);

        $file = <<<'EOD'
<?php
echo '0';
EOD;
        self::putFile(self::subdir, 'test.php', $file);

    }

    /**
     *  Run the test to see if a header can be successfully set using the .htaccess.
     *
     *  @return bool|null  Returns true if it can be established that it works, false if it can
     *                       be established that it does not work, or null if nothing could be
     *                       established due to some other failure
     */
    public function runTest() {

        // TODO: Perhaps create a StandardTester class, which implements this method?
        //$this->runStandardTest();

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
