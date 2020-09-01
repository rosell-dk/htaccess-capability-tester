<?php

namespace HtaccessCapabilityTester;

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

    /**
     * Child classes must implement this method, which tells which subdir to put test files in.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'grant-all-crash-tester';
    }

    /**
     * Get the .htaccess content to crash-test.
     *
     * @return  string  The file content of the .htaccess
     */
    protected function getHtaccessToCrashTest()
    {

        $file = <<<'EOD'
# This .htaccess is here in order to test if it results in a 500 Internal Server Error.
# .htaccess files can result in 500 Internal Server Error when they contain directives that has
# not been allowed for the directory it is in (that stuff is controlled with "AllowOverride" or
# "AllowOverrideList" in httpd.conf)
#
# The use case of a .htaccess file like the one tested here would be an attempt to override
# meassurements taken to prevent access. As an example, in Wordpress, there are security plugins
# which puts "Require all denied" into .htaccess files in certain directories in order to strengthen
# security. Such security meassurements could even be applied to the plugins directory, as plugins
# normally should not need PHPs to be requested directly. But of course, there are cases where plugin
# authors need to anyway and thus find themselves counterfighting the security plugin with an .htaccess
# like this. But in doing so, they run the risk of the 500 Internal Server Error. There are standard
# setups out there which not only does not allow "Require" directives, but are configured to go fatal
# about it.
#
# The following directives is used in this .htaccess file:
# - Require  (Override: AuthConfig)
# - Order (Override: Limit)
# - FilesMatch (Override: All)
# - IfModule  (Override: All)

# FilesMatch should usually be used in this use case, as you would not want to be granting more access
# than you need
<FilesMatch "ping\.txt$">
    <IfModule !mod_authz_core.c>
        Order deny,allow
        Allow from all
    </IfModule>
    <IfModule mod_authz_core.c>
        Require all granted
    </IfModule>
</FilesMatch>
EOD;

        return $file;
    }
}
