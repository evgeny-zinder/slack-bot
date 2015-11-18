<?php

namespace slackbot\models;

use slackbot\Util;

class Variables
{
    public static function set($name, $value)
    {
        $vars = Registry::get('variables');
        $vars[$name] = $value;
        Registry::set('variables', $vars);
    }

    public static function get($name)
    {
        return Util::arrayGet(Registry::get('variables'), $name);
    }

    public static function all()
    {
        return Registry::get('variables');
    }

    public static function remove($name)
    {
        $vars = Registry::get('variables');
        if (isset($vars[$name])) {
            unset($vars[$name]);
        }
        Registry::set('variables', $vars);
    }

    public static function clear()
    {
        Registry::set('variables', []);
    }

}
