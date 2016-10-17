<?php

namespace slackbot\caching;


interface StorageInterface
{
    public function get($key);
    public function all();
    public function set($key, $value);
    public function flush($key);
    public function clear();
}
