<?php

namespace slackbot\util;

class FileLoader
{
    public function load($fileName) {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new \RuntimeException(
                sprintf(
                    'File %s is not accessible',
                    $fileName
                )
            );
        }
        return file_get_contents($fileName);
    }
}
