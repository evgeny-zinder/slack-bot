<?php

namespace slackbot\util;

/**
 * Class Posix
 * Some unix-based tools
 * @package slackbot\util
 */
class Posix
{
    /**
     * Checks if PID is still running
     * @param int $pid
     * @return bool
     */
    public static function isPidActive($pid)
    {
        return 2 === (int) `ps -p $pid | wc -l`;
    }

    /**
     * Executes array of command and returns output of the last one
     * @param array $commands
     * @return string
     */
    public static function execute(array $commands)
    {
        $result = null;
        foreach ($commands as $command) {
            $result = `$command`;
        }
        return $result;
    }
}
