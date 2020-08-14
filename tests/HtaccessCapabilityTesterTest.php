<?php

/**
 * WebPConvert - Convert JPEG & PNG to WebP with PHP
 *
 * @link https://github.com/rosell-dk/webp-convert
 * @license MIT
 */

namespace HtaccessCapabilityTester\Tests;

use HtaccessCapabilityTester\HtaccessCapabilityTester;

use PHPUnit\Framework\TestCase;

class HtaccessCapabilityTesterTest extends TestCase
{

    public function test1()
    {
        $c = 'mod-headers';
        $this->assertEquals($c, HtaccessCapabilityTester::testCapatibility($c));
    }

}
