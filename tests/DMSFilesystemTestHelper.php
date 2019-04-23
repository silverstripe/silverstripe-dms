<?php

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Control\Director;

class DMSFilesystemTestHelper
{
    /**
     * Files that are added to the DMS asset directory by the DMS instance. They will be removed after running tests.
     *
     * @var array
     */
    protected static $dmsFiles = array('.htaccess', 'web.config');

    /**
     * Deletes a directory and all files within it, or a file. Will automatically prepend the base path.
     *
     * This only work while a unit test is running for safety reasons.
     *
     * @param string $path
     */
    public static function delete($path)
    {
        if (!SapphireTest::is_running_test() || !file_exists($path)) {
            return false;
        }

        $path = Director::baseFolder() . DIRECTORY_SEPARATOR . $path;
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $action = $fileinfo->isDir() ? 'rmdir' : 'unlink';
                $action($fileinfo->getRealPath());
            }

            rmdir($path);
        } else {
            unlink($path);
        }
    }
}
