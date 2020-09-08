<?php

namespace HtaccessCapabilityTester;

class SimpleTestFileLineUpper implements TestFilesLineUpperInterface
{
    /**
     * Line-up test files.
     *
     * This method should make sure that the files passed in are there and are up-to-date.
     * - If a file is missing, it should be created.
     * - If a file has changed content, it should be updated
     * - If the directory contains a file/dir that should not be there, it should be removed
     *
     * @param  array  $files   The files that needs to be there
     *
     * @return  void
     */
    public function lineUp($files)
    {
        foreach ($files as $file) {
            $success = true;
            list($filename, $content) = $file;
            $dir = dirname($filename);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new \Exception('Failed creating dir: ' . $dir);
                }
            }
            if (file_exists($filename)) {
                // file already exists, now check if content is the same
                $existingContent = file_get_contents($filename);
                if (($existingContent === false) || ($content != $existingContent)) {
                    $success = file_put_contents($filename, $content);
                }
            } else {
                $success = file_put_contents($filename, $content);
            }
            if (!$success) {
                throw new \Exception('Failed creating file: ' . $filename);
            }
        }
    }
}
