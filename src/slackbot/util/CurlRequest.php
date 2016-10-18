<?php

namespace slackbot\util;

/**
 * Class CurlRequest
 * @package slackbot\util
 */
class CurlRequest
{
    /** @var array */
    protected $curlOptDefaults = [
        CURLOPT_POST => 0,
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPGET => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 1000
    ];

    /**
     * @param string $url
     * @param array $options
     * @return array with info, headers and body
     * @throws \Exception If there is a cURL failure
     */
    public function getCurlResult($url, array $options = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->curlOptDefaults);
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        if ('' !== $curlError) {
            throw new \Exception(
                "Curl error: $curlError"
            );
        }

        $info = curl_getinfo($ch);
        $headers = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);

        $responseData = [
            'info' => $info,
            'headers' => $headers,
            'body' => $body
        ];

        curl_close($ch);

        return $responseData;
    }
}
