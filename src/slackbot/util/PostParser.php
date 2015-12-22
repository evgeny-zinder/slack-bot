<?php

namespace slackbot\util;

/**
 * Class PostParser
 * Tries to parse raw POST data
 * @package slackbot\util
 */
class PostParser
{
    /**
     * @param array $post
     * @return array
     */
    public function parse(array $post)
    {
        $rawData = $this->getRawData($post);
        $delimiter = $this->getDelimiter($rawData);
        $slicedData = $this->getSlicedRawData($rawData, $delimiter);
        $parsedData = $this->getParsedData($slicedData);
        return $parsedData;
    }

    /**
     * @param string $str1
     * @param string $str2
     * @return string
     */
    public function strEquals($str1, $str2) {
        return substr($str1, 0, strlen($str2)) === $str2;
    }

    /**
     * @param array $post
     * @return string
     */
    protected function getRawData(array $post)
    {
        $rawData = '';
        foreach ($post as $key => $value) {
            $rawData .= $key . '=' . $value;
        }
        $rawData = preg_split('/[\n\r]+/', $rawData);
        return $rawData;
    }

    /**
     * @param array $rawData
     * @return string
     */
    protected function getDelimiter(array $rawData)
    {
        return $rawData[0];
    }

    /**
     * @param array $rawData
     * @param string $delimiter
     * @return array
     */
    protected function getSlicedRawData(array $rawData, $delimiter)
    {
        $n = 0;
        $slicedData = [];
        unset($rawData[0]);
        foreach ($rawData as $item) {
            if (substr($item, 0, strlen($delimiter)) === $delimiter) {
                $n++;
                continue;
            }
            $slicedData[$n][] = $item;
        }
        return $slicedData;
    }

    /**
     * @param array $slicedData
     * @return array
     */
    protected function getParsedData($slicedData)
    {
        $data = [];
        foreach ($slicedData as $item) {
            if (substr($item[0], 0, 19) === 'Content-Disposition') {
                preg_match('/.+name=\"([a-zA-Z]+)\"/', $item[0], $matches);
                if (count($matches) === 2) {
                    unset($item[0]);
                    $data[$matches[1]] = implode("\n", $item);
                }
            }
        }
        return $data;
    }
}
