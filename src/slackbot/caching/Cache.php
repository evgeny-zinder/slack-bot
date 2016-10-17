<?php

namespace slackbot\caching;


class Cache implements StorageInterface
{
    protected static $storage;

    public function __construct(StorageInterface $storage)
    {
        self::$storage = $storage;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return self::$storage->get($key);
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return self::$storage;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return self::$storage->all();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function flush($key)
    {
        self::$storage->flush($key);
    }

    /**
     * @return mixed
     */
    public function clear()
    {
        self::$storage->clear();
    }
}
