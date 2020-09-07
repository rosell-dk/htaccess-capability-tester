<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\TestResult;

/**
 * Class for testing if a .htaccess results in a 500 Internal Server Error
 * (ie due to being malformed or containing directives that are unknown or not allowed)
 *
 * The tester reports success when:
 * - A request to a certain file in the directory does not result in a 500 Internal Server Error
 *
 * The tester reports failure when:
 * - A request to a certain file in the directory results in a 500 Internal Server Error
 *
 * The tester reports indeterminate (null) when:
 * - get_headers() call fails (What kind of failure could this be, I wonder?)
 *
 * Notes:
 * - There might be false negatives, as there could be other reasons behind a 501 error than
 *       than a malformed .htaccess.
 * - The tester only reports failure on a 500 Internal Server Error. All other status codes (even server errors)
 *       are treated as a success. The assumption here is that malformed .htaccess files / .htaccess
 *       files containing unknown or disallowed directives always results in a 500
 * - If your purpose is to test if a request succeeds (response 200 Ok), you should create your own class.
 *       (note that if you want to ensure that a php will succeed, make sure that a php is requested)
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class CrashTester extends CustomTester
{

    public function __construct($baseDir, $baseUrl, $htaccessRules, $subDir = null)
    {
        if (is_null($subDir)) {
            $subDir = hash('md5', $htaccessRules);
        }

        $test = [
            'subdir' => $subDir,
            'files' => [
                ['.htaccess', $htaccessRules],
                ['request-me.txt', 'thanks'],
            ],
            'request' => 'request-me.txt',
            'interpretation' => [
                ['failure', 'status-code', 'equals', '500'],
                ['success', 'status-code', 'not-equals', '500'],
            ]
        ];

        parent::__construct($baseDir, $baseUrl, $test);

    }

    /**
     * Child classes must implement this method, which tells which subdir the
     * test files are to be put.
     *
     * @return  string  A subdir for the test files
     */
    public function getSubDir()
    {
        return 'crash-tester';
    }

}
