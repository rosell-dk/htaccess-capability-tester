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

    /* @var string Module id (ie "rewrite") */
    protected $moduleId;

    public function __construct($baseDir, $baseUrl, $moduleId)
    {
        $this->moduleId = $moduleId;

        parent::__construct($baseDir, $baseUrl);
    }

    public function moduleToTest()
    {
        return $this->moduleId;
    }
}
