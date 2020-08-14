<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if rewriting works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class RewriteTester extends AbstractStandardTester
{

    public function __construct($baseDir2, $baseUrl2)
    {
        parent::__construct($baseDir2, $baseUrl2, 'rewrite-tester');
    }

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

        self::putFile('.htaccess', $file);

        $file = <<<'EOD'
<?php
echo '1';
EOD;
        self::putFile('1.php', $file);

        $file = <<<'EOD'
<?php
echo '0';
EOD;
        self::putFile('test.php', $file);

    }
}
