<?php

namespace slackbot\util;

class PostParser
{
    public function parse(array $post)
    {
        $rawData = $this->getRawData($post);
        $delimiter = $this->getDelimiter($rawData);
        $slicedData = $this->getSlicedRawData($rawData, $delimiter);
        $parsedData = $this->getParsedData($slicedData);
        return $parsedData;
    }

    public function strEquals($str1, $str2) {
        return substr($str1, 0, strlen($str2) === $str2);
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
     * @param $rawData
     * @return array
     */
    protected function getDelimiter(array $rawData)
    {
        return $rawData[0];
    }

    /**
     * @param $rawData
     * @param $delimiter
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
     * @param $slicedData
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
