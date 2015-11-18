<?php

namespace slackbot;

class Util
{
    public static function arrayGet($array, $key)
    {
        if (!is_array($array)) return null;
        return array_key_exists($key, $array) ? $array[$key] : null;
    }
}
