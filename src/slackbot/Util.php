<?php

namespace slackbot;

/**
 * Class Util
 * @package slackbot
 */
class Util
{
    /**
     * Error-safe recursive array item getter
     * Translates path "a.b.c" to 'a' => ['b' => ['c' => ... ]]]
     * @param array $array
     * @param string $path
     * @return mixed
     */
    public static function arrayGet($array, $path)
    {
        $path = explode('.', $path);
        foreach ($path as $item) {
            if (!is_array($array) || !array_key_exists($item, $array)) {
                return null;
            }
            $array = $array[$item];
        }
        return $array;
    }
}
