<?php

namespace HtaccessCapabilityTester\Testers;


/**
 * Class for testing if setting request headers in an .htaccess file works.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
abstract class AbstractStandardTester extends AbstractTester
{

    /**
     *  Run the test to see if a header can be successfully set using the .htaccess.
     *
     *  @return bool|null  Returns true if it can be established that it works, false if it can
     *                       be established that it does not work, or null if nothing could be
     *                       established due to some other failure
     */
    public function runTest() {
        $this->createTestFiles();

        $responseText = self::makeHTTPRequest($this->baseUrl . '/' . $this->subDir . '/test.php');
        if ($responseText == '1') {
            return true;
        };
        if ($responseText == '0') {
            return false;
        };
    }
}
