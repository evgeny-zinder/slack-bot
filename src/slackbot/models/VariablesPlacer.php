<?php

namespace slackbot\models;

class VariablesPlacer
{
    /** @var string */
    private $text;

    /** @var array */
    private $vars;

    public function setText($text) {
        $this->text = $text;
    }

    public function setVars(array $vars) {
        $this->vars = $vars;
    }

    public function place()
    {
        $text = $this->text;
        foreach ($this->vars as $name => $value) {
            $name = '%' . $name . '%';
            $text = str_replace($name, $value, $text);
        }
        return $text;
    }
}
