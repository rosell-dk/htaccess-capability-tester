<?php

namespace HtaccessCapabilityTester\Testers;


/**
 * Abstract class for making it easy to test if a .htaccess results in a 500 Internal Server Error
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
abstract class AbstractCrashTester extends AbstractStandardTester
{

    /**
     * Creates the neccessary test files.
     *
     * @return  void
     */
    public function createTestFiles() {


        $file = $this->getHtaccessToCrashTest();

        self::putFile('.htaccess', $file, 'subtest');

        $file = <<<'EOD'
<?php
echo '1';
EOD;
        self::putFile('subtest.php', $file, 'subtest');

        // The test.php file will test if the subtest "crashes" or not
        $file = '<?php' . "\n" .
            '$response = file_get_contents(\'' . $this->baseUrl . '/' . $this->subDir . '/subtest/subtest.php' . '\');' . "\n" .
            'echo ($response === false ? 0 : $response);' . "\n";

        self::putFile('test.php', $file);

    }
}
