<?php
/*
subdir: rewrite-tester
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
Expected behaviour:

Server setup                   |  Test result
--------------------------------------------------
.htaccess disabled             |  failure
forbidden directives (fatal)   |  failure
forbidden directives (silent)  |  failure
required module not loaded     |  failure
access denied                  |  inconclusive  (it might be allowed to other files)
otherwise                      |  success
*/


namespace HtaccessCapabilityTester\Tests\Testers;

use HtaccessCapabilityTester\Testers\RewriteTester;

use HtaccessCapabilityTester\Tests\FakeServer;

use PHPUnit\Framework\TestCase;

class RewriteTesterTest extends BasisTestCase
{

    public function test1()
    {


        $expectedBehaviour = [
            //'htaccessDisabled' => 'failure',
            'directivesForbiddenAndFatal' => 'failure',
            'accessDenied' => 'inconclusive'
        ];

        $fakeServer = new FakeServer();

        foreach ($expectedBehaviour as $serverConfig => $expectedResult) {
            $fakeServer->config($serverConfig);
            $testResult = $fakeServer->runTester(new RewriteTester());
            switch ($expectedResult) {
                case 'failure':
                    $this->assertFailure($testResult);
                    break;
                case 'success':
                    $this->assertSuccess($testResult);
                    break;
                case 'inconclusive':
                    $this->assertInconclusive($testResult);
                    break;
            }

        }

    }

}
