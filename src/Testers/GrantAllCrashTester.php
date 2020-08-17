<?php

namespace HtaccessCapabilityTester\Testers;

/**
 * Class for testing if granting access works (doesn't result in a 500 Internal Server Error).
 *
 * It is not uncommon to see .htaccess files that are put in a folder in order
 * to override access restrictions that possible have been added to a parent
 * .htaccess files by ie a security plugin.
 * However, such practise can lead to problems because some some servers may
 * have been configured to not allow access configurations in .htaccess files.
 * If that is the case, the result is a 500 Internal Server Error.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class GrantAllCrashTester extends AbstractCrashTester
{
    
    public function __construct($baseDir2, $baseUrl2)
    {
        parent::__construct($baseDir2, $baseUrl2, 'grant-all-not-crash-tester');
    }

    /**
     * Get the .htaccess content to crash-test.
     *
     * @return  void
     */
    public function getHtaccessToCrashTest() {

        $file = <<<'EOD'
<FilesMatch "1\.php$">
  <IfModule !mod_authz_core.c>
    Order deny,allow
    Allow from all
  </IfModule>
  <IfModule mod_authz_core.c>
    # The following "Require" will result in a 500 Internal Server Error
    # Unless AuthConfig has been allowed in .htaccess files (ie by a "AllowOverride All"
    # or a "AllowOverride AuthConfig")
    Require all granted
  </IfModule>
</FilesMatch>
EOD;

        return $file;
    }
}
