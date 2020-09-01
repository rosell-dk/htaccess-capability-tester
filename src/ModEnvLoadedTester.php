<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if setting request headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class ModEnvLoadedTester extends AbstractModLoadedTester
{

    /**
     * Child classes must implement this method, which tells which module
     * should be tested (ie "headers", "rewrite" or such)
     *
     * @return  string  The module to test (ie "headers", "rewrite" or such)
     */
    public function moduleToTest()
    {
        return 'env';
    }
}
