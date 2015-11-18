<?php

namespace slackbot\models;

use slackbot\Util;

class ConditionResolver
{
    public function isConditionMet($condition, array $variables) {
        preg_match('/^\s*(\$[a-zA-Z\_]+)\s*([\!\<\=\>\%]+)\s*(.+)$/', $condition, $matches);

        if (count($matches) != 4) {
            throw new \LogicException('Error parsing loop condition');
        }

        $variableValue = (int) Util::arrayGet($variables, $matches[1]);
        $result = (int) $matches[3];
        switch ($matches[2]) {
            case '=' : return $variableValue == $result;
            case '==' : return $variableValue == $result;
            case '>' : return $variableValue > $result;
            case '>=' : return $variableValue >= $result;
            case '<' : return $variableValue < $result;
            case '<=' : return $variableValue <= $result;
            case '<>' : return $variableValue != $result;
            case '!=' : return $variableValue != $result;
            case '%' : return ($variableValue + $result) % $result === 0;
            case '!%' : return ($variableValue + $result) % $result !== 0;
            default: throw new \LogicException('Error parsing comparsion operator');
        }
    }
}
