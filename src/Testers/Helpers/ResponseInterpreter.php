<?php

namespace HtaccessCapabilityTester\Testers\Helpers;

use \HtaccessCapabilityTester\HttpResponse;
use \HtaccessCapabilityTester\TestResult;
use \HtaccessCapabilityTester\Testers\AbstractTester;

/**
 * Class for interpreting responses using a defined interpretation table.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class ResponseInterpreter
{

    /**
     * Parse status string (failure | success | inconclusive) to bool|null.
     *
     * @param  string  $statusString  (failure | success | inconclusive)
     * @return bool|null
     */
    private static function parseStatusString($statusString)
    {
        $status = null;
        switch ($statusString) {
            case 'failure':
                $status = false;
                break;
            case 'inconclusive':
                $status = null;
                break;
            case 'success':
                $status = true;
                break;
        }
        return $status;
    }

    /**
     * Evaluate condition (string examination)
     *
     * @param  string  $val
     * @param  string  $operator  (is-empty | equals | not-equals | begins-with)
     * @param  string  $arg1  (only required for some operators)
     * @return bool
     */
    private static function evaluateConditionForString($operator, $val, $arg1)
    {
        switch ($operator) {
            case 'is-empty':
                return ($val == '');
            case 'equals':
                return ($val == $arg1);
            case 'not-equals':
                return ($val != $arg1);
            case 'begins-with':
                return (strpos($val, $arg1) === 0);
        }
        return false;
    }

    /**
     * Evaluate condition  (hash examination)
     *
     * @param  array  $val
     * @param  string $operator  (is-empty | equals | not-equals | begins-with)
     * @param  string $arg1  (only required for some operators)
     * @return bool
     */
    private static function evaluateConditionForHash($operator, $val, $arg1, $arg2)
    {
        switch ($operator) {
            case 'contains-key':
                return (isset($val[$arg1]));
            case 'not-contains-key':
                return (!isset($val[$arg1]));
            case 'contains-key-value':
                return (isset($val[$arg1]) && ($val[$arg1] == $arg2));
            case 'not-contains-key-value':
                return (!isset($val[$arg1]) || ($val[$arg1] != $arg2));
        }
        return false;
    }

    /**
     * Interpret a response using an interpretation table.
     *
     * @param HttpResponse    $response
     * @param array           $interpretationTable
     *
     * @return TestResult   If there is no match, the test result will have status = false and
     *                      info = "no-match".
     */
    public static function interpret($response, $interpretationTable)
    {
        foreach ($interpretationTable as $i => $entry) {
            // ie:
            // ['inconclusive', 'body', 'is-empty'],
            // ['failure', 'statusCode', 'equals', '500']
            // ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', 'test'],

            $status = self::parseStatusString($entry[0]);

            if (!isset($entry[1])) {
                return new TestResult($status, '');
            }

            $propertyToExamine = $entry[1];
            $operator = $entry[2];
            $arg1 = (isset($entry[3]) ? $entry[3] : '');
            $arg2 = (isset($entry[4]) ? $entry[4] : '');

            $val = '';
            switch ($propertyToExamine) {
                case 'status-code':
                    $val = $response->statusCode;
                    break;
                case 'body':
                    $val = $response->body;
                    break;
                case 'headers':
                    $val = $response->getHeadersHash();
                    break;
            }

            $reason = $propertyToExamine . ' ' . $operator;
            if (isset($entry[3])) {
                $reason .= ' "' . implode('" "', array_slice($entry, 3)) . '"';
            }
            if (($propertyToExamine == 'status-code') && ($operator == 'not-equals')) {
                $reason .= ' - it was: ' . $val;
            }
            $result = new TestResult($status, $reason);

            if ($propertyToExamine == 'headers') {
                $match =  self::evaluateConditionForHash($operator, $val, $arg1, $arg2);
            } else {
                $match = self::evaluateConditionForString($operator, $val, $arg1);
            }
            if ($match) {
                return $result;
            }
        }
        return new TestResult(null, 'no-match');
    }
}
