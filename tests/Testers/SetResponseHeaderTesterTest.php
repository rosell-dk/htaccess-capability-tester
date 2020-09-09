<?php
/*
subdir: set-response-header-tester
files:
    - filename: '.htaccess'
      content: |
          <IfModule mod_headers.c>
              Header set X-Response-Header-Test: test
          </IfModule>
    - filename: 'request-me.txt'
      content: 'they needed someone, so here i am'

request:
    url: 'request-me.txt'

interpretation:
    - [success, headers, contains-key-value, 'X-Response-Header-Test', 'test'],
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
use HtaccessCapabilityTester\Testers\SetResponseHeaderTester;
use HtaccessCapabilityTester\Tests\FakeServer;
use PHPUnit\Framework\TestCase;

class SetResponseHeaderTesterTest extends BasisTestCase
{

    public function testHtaccessDisabled()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disableHtaccess();
        $testResult = $fakeServer->runTester(new SetResponseHeaderTester());
        $this->assertFailure($testResult);
    }

    public function testDisallowedDirectivesFatal()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disallowAllDirectives('fatal');
        $testResult = $fakeServer->runTester(new SetResponseHeaderTester());
        $this->assertFailure($testResult);
    }

    public function testAccessAllDenied()
    {
        $fakeServer = new FakeServer();
        $fakeServer->denyAllAccess();
        $testResult = $fakeServer->runTester(new SetResponseHeaderTester());
        $this->assertInconclusive($testResult);
    }

    /**
     * Test when the directive has no effect.
     * This could happen when:
     * - The directive is forbidden (non-fatal)
     * - The module is not loaded
     */
    public function testDirectiveHasNoEffect()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/set-response-header-tester/request-me.txt' => new HttpResponse('hi', '200', [])
        ]);
        $testResult = $fakeServer->runTester(new SetResponseHeaderTester());
        $this->assertFailure($testResult);
    }

    public function testSuccess()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/set-response-header-tester/request-me.txt' => new HttpResponse('hi', '200', ['X-Response-Header-Test: test'])
        ]);
        $testResult = $fakeServer->runTester(new SetResponseHeaderTester());
        $this->assertSuccess($testResult);
    }

}
