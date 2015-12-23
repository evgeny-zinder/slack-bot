<?php

namespace slackbot\util {
    function file_get_contents()
    {
        global $mockFileGetContentsSuccess;
        if (isset($mockFileGetContentsSuccess) && $mockFileGetContentsSuccess === true) {
            return 'Some file content';
        } else {
            return call_user_func_array('\file_get_contents', func_get_args());
        }
    }

    function file_exists()
    {
        global $mockFileGetContentsSuccess;
        if (isset($mockFileGetContentsSuccess) && $mockFileGetContentsSuccess === true) {
            return true;
        } else {
            return call_user_func_array('\file_exists', func_get_args());
        }
    }

    function is_readable()
    {
        global $mockFileGetContentsSuccess;
        if (isset($mockFileGetContentsSuccess) && $mockFileGetContentsSuccess === true) {
            return true;
        } else {
            return call_user_func_array('\is_readable', func_get_args());
        }
    }
}

namespace tests\util {

    use slackbot\util\FileLoader;

    class FileLoaderTest extends \PHPUnit_Framework_TestCase
    {
        /** @test */
        public function shouldLoadNormalFile()
        {
            global $mockFileGetContentsSuccess;
            $mockFileGetContentsSuccess = true;

            $loader = new FileLoader();
            $result = $loader->load('file.txt');

            $this->assertEquals('Some file content', $result);
            $mockFileGetContentsSuccess = false;
        }

        /**
         * @test
         */
        public function shouldThrowExceptionOnNonExistentFile()
        {
            $this->setExpectedException('\Exception', 'File file.txt is not accessible');

            $loader = new FileLoader();
            $loader->load('file.txt');
        }

    }
}
