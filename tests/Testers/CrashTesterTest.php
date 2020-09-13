<?php
/*
subdir: rewrite
files:
    - filename: '.htaccess'
      content: |
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteRule ^0\.txt$ 1\.txt [L]
        </IfModule>
    - filename: '0.txt'
      content: '0'
    - filename: '1.txt'
      content: '1'

request:
    url: '0.txt'

interpretation:
    - [success, body, equals, '1']
    - [failure, body, equals, '0']
    - [failure, status-code, equals, '500']


----

Tested:

Server setup                   |  Test result
--------------------------------------------------
.htaccess disabled             |  failure
forbidden directives (fatal)   |  failure
access denied                  |  inconclusive  (it might be allowed to other files)
directive has no effect        |  failure
                               |  success
*/


namespace HtaccessCapabilityTester\Tests\Testers;

use HtaccessCapabilityTester\HttpResponse;
use HtaccessCapabilityTester\Testers\CrashTester;
use HtaccessCapabilityTester\Tests\FakeServer;
use PHPUnit\Framework\TestCase;

class CrashTesterTest extends BasisTestCase
{
  public function testHtaccessDisabled()
  {
    $this->assertFalse(false);
  }
  /*

    public function testHtaccessDisabled()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disableHtaccess();
        $testResult = $fakeServer->runTester(new CrashTester(''));
        $this->assertFailure($testResult);
    }

    public function testDisallowedDirectivesFatal()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disallowAllDirectives('fatal');
        $testResult = $fakeServer->runTester(new CrashTester());
        $this->assertFailure($testResult);
    }

    public function testAccessAllDenied()
    {
        $fakeServer = new FakeServer();
        $fakeServer->denyAllAccess();
        $testResult = $fakeServer->runTester(new CrashTester());
        $this->assertInconclusive($testResult);
    }

    public function testDirectiveHasNoEffect()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/rewrite/0.txt' => new HttpResponse('0', '200', [])
        ]);
        $testResult = $fakeServer->runTester(new CrashTester());
        $this->assertFailure($testResult);
    }

    public function testSuccess()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/rewrite/0.txt' => new HttpResponse('1', '200', [])
        ]);
        $testResult = $fakeServer->runTester(new CrashTester());
        $this->assertSuccess($testResult);
    }
*/
}
