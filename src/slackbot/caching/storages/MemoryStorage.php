<?php

namespace slackbot\caching\storages;


use eznio\ar\Ar;
use slackbot\caching\StorageInterface;

class MemoryStorage implements StorageInterface
{
    protected static $data = [];

    public function get($key)
    {
        return Ar::get(self::$data, $key);
    }

    public function all()
    {
        return self::$data;
    }

    public function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public function flush($key)
    {
        unset(self::$data[$key]);
    }

    public function clear()
    {
        self::$data = [];
    }
}
