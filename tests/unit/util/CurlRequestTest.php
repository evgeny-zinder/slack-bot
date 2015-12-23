<?php

namespace slackbot\util {
    function curl_exec()
    {
        global $mockCurlExec;
        if (isset($mockCurlExec) && $mockCurlExec === true) {
            return '<body></body>';
        } else {
            return call_user_func_array('\curl_exec', func_get_args());
        }
    }

    function curl_error()
    {
        global $mockCurlError;
        if (isset($mockCurlError) && $mockCurlError === true) {
            return 'cURL timeout';
        } else {
            return call_user_func_array('\curl_error', func_get_args());
        }
    }

}

namespace tests\util {

    use slackbot\util\CurlRequest;

    class CurlRequestTest extends \PHPUnit_Framework_TestCase
    {
        /** @test */
        public function shouldExecuteGetRequests()
        {
            global $mockCurlExec;
            $mockCurlExec = true;

            $curlRequest = new CurlRequest();
            $response = $curlRequest->getCurlResult('http://ya.ru');
            $this->assertArrayHasKey('info', $response);
            $this->assertArrayHasKey('headers', $response);
            $this->assertArrayHasKey('body', $response);
            $this->assertEquals('<body></body>', $response['body']);
            $mockCurlExec = false;
        }

        /**
         * @test
         */
        public function shouldThrowExceptionOnError()
        {
            global $mockCurlError;
            $mockCurlError = true;
            $this->setExpectedException('\Exception', 'Curl error: cURL timeout');

            $curlRequest = new CurlRequest();
            $curlRequest->getCurlResult('non-existent-url');
            $mockCurlError = false;
        }

    }
}
