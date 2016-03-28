<?php

namespace slackbot\models;

use slackbot\Util;

/**
 * Class ArgvParser
 * Parses command-line script arguments
 * @package slackbot\models
 */
class ArgvParser
{
    /** @var array */
    private $argv;

    /** @var array */
    private $args;

    /**
     * ArgvParser constructor.
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->args = [];
        $this->parse();
    }

    /**
     * Processes parsing
     * @param array $argv
     */
    public function parse(array $argv = [])
    {
        if ($argv !== []) {
            $this->argv = $argv;
        }

        foreach ($this->argv as $arg) {
            $arg = str_replace('--', '', $arg);
            $argData = explode('=', $arg);
            if (2 === count($argData) && '' !== $argData[0] && '' !== $argData[1]) {
                $this->args[$argData[0]] = $argData[1];
            }
        }
    }

    /**
     * Returns array with all params passed
     * @return array
     */
    public function all()
    {
        return $this->args;
    }

    /**
     * Returns named param or null
     * @param string $name
     * @return null
     */
    public function get($name)
    {
        return Util::arrayGet($this->args, $name);
    }
}
