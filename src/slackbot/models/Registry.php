<?php

namespace slackbot\models;

use eznio\ar\Ar;

/**
 * Class Registry
 * @package slackbot\models
 */
class Registry
{
    /** @var array */
    private static $data = [];

    /**
     * Returns registry key
     * @param string $key key name
     * @return mixed
     */
    public static function get($key)
    {
        return Ar::get(self::$data, $key);
    }

    /**
     * Sets registry key
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }
}
