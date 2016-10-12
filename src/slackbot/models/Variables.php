<?php

namespace slackbot\models;

use eznio\ar\Ar;

/**
 * Class Variables
 * @package slackbot\models
 */
class Variables
{
    /**
     * Sets variable value
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        $vars = Registry::get('variables');
        $vars[$name] = $value;
        Registry::set('variables', $vars);
    }

    /**
     * Returns variable value
     * @param string $name
     * @return string|null
     */
    public static function get($name)
    {
        return Ar::get(Registry::get('variables'), $name);
    }

    /**
     * Gets associative array of all variables in "name => value" format
     * @return array
     */
    public static function all()
    {
        return Registry::get('variables') ?: [];
    }

    /**
     * Unset variable
     * @param string $name
     */
    public static function remove($name)
    {
        $vars = Registry::get('variables');
        if (isset($vars[$name])) {
            unset($vars[$name]);
        }
        Registry::set('variables', $vars);
    }

    /**
     * Unset all variables
     */
    public static function clear()
    {
        Registry::set('variables', []);
    }
}
