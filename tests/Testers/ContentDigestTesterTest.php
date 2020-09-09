<?php
/*
subdir: content-digest-tester
subtests:
  - subdir: on
    files:
    - filename: '.htaccess'
      content: |
        ContentDigest On
    - filename: 'request-me.txt'
      content: 'hi'
    request:
      url: 'request-me.txt'
    interpretation:
      - ['failure', 'status-code', 'equals', '500'],
      - ['inconclusive', 'status-code', 'not-equals', '200'],    // calls the whole thing off
      - ['failure', 'headers', 'not-contains-key', 'Content-MD5'],

    - subdir: off
      files:
        - filename: '.htaccess'
          content: |
             ContentDigest Off
        - filename: 'request-me.txt'
          content: 'hi'
      request:
        url: 'request-me.txt'

      interpretation:
        - ['failure', 'status-code', 'equals', '500']
        - ['failure', 'headers', 'contains-key', 'Content-MD5']
        - ['inconclusive', 'status-code', 'not-equals', '200']
        - ['success', 'status-code', 'equals', '200']
----

Tested:

Server setup                   |  Test result
--------------------------------------------------
.htaccess disabled             |  failure
forbidden directives (fatal)   |  failure       (highly unlikely, as it is part of core - but still possible)
access denied                  |  inconclusive  (it might be allowed to other files)
directive has no effect        |  failure
                               |  success

*/


namespace HtaccessCapabilityTester\Tests\Testers;

use HtaccessCapabilityTester\HttpResponse;
use HtaccessCapabilityTester\Testers\ContentDigestTester;
use HtaccessCapabilityTester\Tests\FakeServer;
use PHPUnit\Framework\TestCase;

class ContentDigestTesterTest extends BasisTestCase
{

    public function testHtaccessDisabled()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disableHtaccess();
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertFailure($testResult);
    }

    public function testDisallowedDirectivesFatal()
    {
        $fakeServer = new FakeServer();
        $fakeServer->disallowAllDirectives('fatal');
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertFailure($testResult);
    }

    public function testAccessAllDenied()
    {
        $fakeServer = new FakeServer();
        $fakeServer->denyAllAccess();
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertInconclusive($testResult);
    }

    /**
     * Test when the directive has no effect.
     * This could happen when:
     * - The directive is forbidden (non-fatal)
     * - The module is not loaded
     *
     * Test no effect when server is setup to content-digest
     */
    public function testDirectiveHasNoEffect1()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/content-digest-tester/on/request-me.txt' => new HttpResponse('hi', '200', ['Content-MD5: aaoeu']),
            '/content-digest-tester/off/request-me.txt' => new HttpResponse('hi', '200', ['Content-MD5: aaoeu']),
        ]);
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertFailure($testResult);
    }

    /** Test no effect when server is setup NOT to content-digest
     */
    public function testDirectiveHasNoEffect2()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/content-digest-tester/on/request-me.txt' => new HttpResponse('hi', '200', []),
            '/content-digest-tester/off/request-me.txt' => new HttpResponse('hi', '200', []),
        ]);
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertFailure($testResult);
    }

    public function testSuccess()
    {
        $fakeServer = new FakeServer();
        $fakeServer->setResponses([
            '/content-digest-tester/on/request-me.txt' => new HttpResponse('hi', '200', ['Content-MD5: aaoeu']),
            '/content-digest-tester/off/request-me.txt' => new HttpResponse('hi', '200', [])
        ]);
        $testResult = $fakeServer->runTester(new ContentDigestTester());
        $this->assertSuccess($testResult);
    }

}
