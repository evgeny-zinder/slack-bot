<?php

namespace slackbot\caching;


class ApiCache
{
    protected static $storage;

    public function __construct(StorageInterface $storage)
    {
        self::$storage = $storage;
    }

    /**
     * @param $path
     * @param $options
     * @return mixed
     */
    public function get($path, $options)
    {
        return self::$storage->get($this->key($path, $options));
    }

    /**
     * @param $path
     * @param $options
     * @param $payload
     * @return mixed
     */
    public function set($path, $options, $payload)
    {
        return self::$storage->set($this->key($path, $options), $payload);
    }

    /**
     * @param $path
     * @param $options
     * @return mixed
     */
    public function flush($path, $options)
    {
        return self::$storage->get($this->key($path, $options));
    }

    /**
     * @return mixed
     */
    public function clear()
    {
        self::$storage->clear();
    }

    /**
     * @param $path
     * @param $options
     * @return string
     */
    protected function key($path, $options)
    {
        return sha1($path . '|' . implode('|', $options));
    }
}
