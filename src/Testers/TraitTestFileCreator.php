<?php

namespace HtaccessCapabilityTester\Testers;

use \HtaccessCapabilityTester\TestResult;

/**
 * Trait for creating test files
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
trait TraitTestFileCreator
{

    public function createTestFilesIfNeeded()
    {
        if (isset($this->testFiles)) {
            foreach ($this->testFiles as list($fileName, $content)) {
                self::createTestFileIfNeeded($fileName, $content);
            }
        }
    }

    /** Create/update test file if needed (missing or changed)
     *
     *  @param  string  $fileName  filname. May contain path components (ie: "subdir/.htaccess")
     *  @param  string  $content   Content of the file
     *
     *  @return bool  Success or not
     */
    private function createTestFileIfNeeded($fileName, $content)
    {
        $path = $this->baseDir . '/' . $fileName;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (!file_exists($path)) {
            return file_put_contents($path, $content);
        }
        // file already exists, now check if content is the same
        $existingContent = file_get_contents($path);
        if ($existingContent === false) {
            return false;
        }
        if ($content != $existingContent) {
            return file_put_contents($path, $content);
        }
        return false;
    }
}
