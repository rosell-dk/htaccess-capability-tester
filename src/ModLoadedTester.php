<?php

namespace HtaccessCapabilityTester;

/**
 * Class for testing if setting request headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class ModLoadedTester extends AbstractModLoadedTester
{

    /* @var string A valid Apache module name (ie "rewrite") */
    protected $moduleName;

    public function __construct($baseDir, $baseUrl, $moduleName)
    {
        $this->moduleName = $moduleName;

        parent::__construct($baseDir, $baseUrl);
    }

    public function moduleToTest()
    {
        return $this->moduleName;
    }
}
