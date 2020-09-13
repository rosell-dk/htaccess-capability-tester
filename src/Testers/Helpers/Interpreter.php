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
class Interpreter
{

    /**
     * Interpret a response using an interpretation table.
     *
     * @param AbstractTester  $tester
     * @param HttpResponse    $response
     * @param array           $interpretationTable
     *
     * @return TestResult   If there is no match, the test result will have status = false and
     *                      info = "no-match".
     */
    public static function interpret($tester, $response, $interpretationTable)
    {
        foreach ($interpretationTable as $i => $entry) {
            // ie:
            // ['inconclusive', 'body', 'is-empty'],
            // ['failure', 'statusCode', 'equals', '500']
            // ['success', 'headers', 'contains-key-value', 'X-Response-Header-Test', 'test'],

            $statusStr = $entry[0];

            $status = null;
            switch ($statusStr) {
                case 'failure':
                    $status = false;
                    break;
                case 'inconclusive':
                    $status = null;
                    break;
                case 'success':
                    $status = true;
                    break;
                case 'handle-errors':
                    if ($response->statusCode == '403') {
                        return new TestResult(null, '403 Forbidden');
                    } elseif ($response->statusCode == '500') {
                        $hct = $tester->getHtaccessCapabilityTester();

                        // Run innocent request / get it from cache. This sets
                        // $statusCodeOfLastRequest, which we need now
                        $hct->innocentRequestWorks();
                        if ($hct->statusCodeOfLastRequest == '500') {
                            return new TestResult(null, 'Errored with 500. Everything errors with 500.');
                        } else {
                            return new TestResult(
                                false,
                                'Errored with 500. ' .
                                'Not all goes 500, so it must be a forbidden directive in the .htaccess'
                            );
                        }
                    } else {
                        continue 2;
                    }
            }

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

            switch ($operator) {
                case 'is-empty':
                    if ($val == '') {
                        return $result;
                    }
                    break;
                case 'equals':
                    if ($val == $arg1) {
                        return $result;
                    }
                    break;
                case 'not-equals':
                    if ($val != $arg1) {
                        return $result;
                    }
                    break;
                case 'begins-with':
                    if (strpos($val, $arg1) === 0) {
                        return $result;
                    }
                    break;
                case 'contains-key':
                    if (isset($val[$arg1])) {
                        return $result;
                    }
                    break;
                case 'not-contains-key':
                    if (!isset($val[$arg1])) {
                        return $result;
                    }
                    break;
                case 'contains-key-value':
                    if (isset($val[$arg1]) && ($val[$arg1] == $arg2)) {
                        return $result;
                    }
                    break;
                case 'not-contains-key-value':
                    if (!isset($val[$arg1]) || ($val[$arg1] != $arg2)) {
                        return $result;
                    }
                    break;
            }
        }
        return new TestResult(null, 'no-match');
    }
}
