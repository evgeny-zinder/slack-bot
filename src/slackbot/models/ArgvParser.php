<?php

namespace slackbot\models;

use slackbot\Util;

class ArgvParser
{
    /** @var array */
    private $argv;

    /** @var array */
    private $args;

    public function __construct(array $argv) {
        $this->argv = $argv;
        $this->parse();
    }

    public function parse(array $argv = []) {
        if ($argv !== []) {
            $this->argv = $argv;
        }

        foreach ($this->argv as $arg) {
            $arg = str_replace('--', '', $arg);
            $argData = explode('=', $arg);
            if (count($argData) === 2) {
                $this->args[$argData[0]] = $argData[1];
            }
        }
    }

    public function all()
    {
        return $this->args;
    }

    public function get($name) {
        return Util::arrayGet($this->args, $name);
    }
}
