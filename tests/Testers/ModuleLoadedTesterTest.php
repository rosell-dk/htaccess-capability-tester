<?php
/*

subdir: module-loaded
subtests:
  - subdir: server-signature
    requirements: htaccessEnabled()
    files:
    - filename: '.htaccess'
      content: |
          ServerSignature Off
          <IfModule mod_xxx.c>
          ServerSignature On
          </IfModule>

    - filename: 'test.php'
      content: |
          <?php
          if (isset($_SERVER['SERVER_SIGNATURE']) && ($_SERVER['SERVER_SIGNATURE'] != '')) {
              echo 1;
          } else {
              echo 0;
          }
    interpretation:
    - ['success', 'body', 'equals', '1']
    - ['failure', 'body', 'equals', '0']
  - subdir: rewrite
    ...
----

Tested:

Server setup                   |  Test result
--------------------------------------------------
.htaccess disabled             |  failure
access denied                  |  inconclusive  (it might be allowed to other files)
it works                       |  success
*/


namespace HtaccessCapabilityTester\Tests\Testers;

use HtaccessCapabilityTester\HttpResponse;
use HtaccessCapabilityTester\Testers\ModuleLoadedTester;
use HtaccessCapabilityTester\Tests\FakeServer;
use PHPUnit\Framework\TestCase;

class ModuleLoadedTesterTest extends BasisTestCase
{

    public function testHtaccessDisabled()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disableHtaccess();
        $testResult = $fakeServer->runTester(new ModuleLoadedTester('setenvif'));
        $this->assertFailure($testResult);
    }

  /**
   * Test inconclusive when all crashes.
   */
  public function testInconclusiveWhenAllCrashes()
  {
      $fakeServer = new FakeServer();

      $fakeServer->makeAllCrash();
      $testResult = $fakeServer->runTester(new ModuleLoadedTester('setenvif'));

      $this->assertInconclusive($testResult);
  }


}
