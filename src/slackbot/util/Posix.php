<?php

namespace slackbot\util;

class Posix
{
    public static function isPidActive($pid)
    {
        return `ps -p $pid | wc -l` == 2;
    }

    public static function execute(array $commands) {
        foreach ($commands as $command) {
            $result = `$command`;
        }
        return $result;
    }
}
