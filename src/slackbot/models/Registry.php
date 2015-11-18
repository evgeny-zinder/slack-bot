<?php

namespace slackbot\models;

use slackbot\Util;

class Registry
{
    private static $data = [];

    public static function get($key) {
        return Util::arrayGet(self::$data, $key);
    }

    public static function set($key, $value) {
        self::$data[$key] = $value;
    }

}
