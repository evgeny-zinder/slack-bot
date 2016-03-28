<?php

namespace slackbot\models;

/**
 * Class VariablesPlacer
 * Replaces placeholders (%NAME%) with given variables list
 * @package slackbot\models
 */
class VariablesPlacer
{
    /** @var string */
    private $text;

    /** @var array */
    private $vars;

    /**
     * Sets text to process
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Sets variables list to substitute in "name => value" format
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * Processes variables placement
     * @return string
     */
    public function place()
    {
        $text = $this->text;
        foreach ($this->vars as $name => $value) {
            $text = str_replace('%' . $name . '%', $value, $text);
        }
        return $text;
    }
}
