<?php

namespace slackbot\util;

/**
 * Class FileLoader
 * Performs file loading from local FS
 * @package slackbot\util
 */
class FileLoader
{
    /**
     * @param string $fileName
     * @return string
     */
    public function load($fileName)
    {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new \RuntimeException(sprintf('File %s is not accessible', $fileName));
        }
        return file_get_contents($fileName);
    }
}
