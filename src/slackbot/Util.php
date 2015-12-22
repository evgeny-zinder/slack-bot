<?php

namespace slackbot;

/**
 * Class Util
 * @package slackbot
 */
class Util
{
    /**
     * Error-safe array item getter
     * @param array $array
     * @param string $key
     * @return string|null
     */
    public static function arrayGet($array, $key)
    {
        if (!is_array($array)) return null;
        return array_key_exists($key, $array) ? $array[$key] : null;
    }
}
