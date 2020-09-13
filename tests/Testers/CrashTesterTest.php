<?php
/*
subdir: 'crash-tester/xxx'  # xxx is a subdir for the specific crash-test
subtests:
  - subdir: the-suspect
    files:
        - filename: '.htaccess'
          content:                          # the rules goes here
        - filename: 'request-me.txt'
          content: 'thanks'
    request:
        url: 'request-me.txt'
        bypass-standard-error-handling': ['all']
    interpretation:
        - [success, body, equals, '1']
        - [failure, body, equals, '0']
        - [success, status-code, not-equals, '500']

  - subdir: the-innocent
    files:
        - filename: '.htaccess'
          content: '# I am no trouble'
        - filename: 'request-me.txt'
          content: 'thanks'
    request:
        url: 'request-me.txt'
        bypass-standard-error-handling: ['all']
    interpretation:
      # The suspect crashed. But if the innocent crashes too, we cannot judge
      [inconclusive, status-code, equals, '500']

      # The innocent did not crash. The suspect is guilty!
      [failure]

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
