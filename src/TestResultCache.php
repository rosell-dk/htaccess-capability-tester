<?php

namespace HtaccessCapabilityTester;

/**
 * Class caching test results
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class TestResultCache
{

    /* @var array   Array for caching */
    public static $cache;

    /**
     *
     * @param  AbstractTester  $tester
     * @param  TestResult      $testResult  The test result
     *
     * @return void
     */
    public static function cache($tester, $testResult)
    {
        if (is_null(self::$cache)) {
            self::$cache = [];
        }
        $key1 = $tester->getBaseDir() . $tester->getBaseUrl();

        if (!isset(self::$cache[$key1])) {
            self::$cache[$key1] = [];
        }

        $key2 = $tester->getCacheKey();
        self::$cache[$key1][$key2] = $testResult;
    }

    /**
     * Check if in cache.
     *
     * @param  AbstractTester  $tester
     *
     * @return bool
     */
    public static function isCached($tester)
    {
        if (is_null(self::$cache)) {
            return false;
        }
        $key1 = $tester->getBaseDir() . $tester->getBaseUrl();
        if (!isset(self::$cache[$key1])) {
            return false;
        }
        $key2 = $tester->getCacheKey();
        if (!isset(self::$cache[$key1][$key2])) {
            return false;
        }
        return true;
    }

    /**
     * Get from cache.
     *
     * @param  AbstractTester  $tester
     *
     * @return TestResult   The test result
     */
    public static function getCached($tester)
    {
        if (!self::isCached($tester)) {
            throw new \Exception('Not in cache');
        } else {
        }
        $key1 = $tester->getBaseDir() . $tester->getBaseUrl();
        $key2 = $tester->getCacheKey();

        return self::$cache[$key1][$key2];
    }
}
