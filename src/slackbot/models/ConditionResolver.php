<?php

namespace slackbot\models;

use eznio\ar\Ar;

/**
 * Class ConditionResolver
 * Parses logical condition and evaluates if it's true or false
 * @package slackbot\models
 */
class ConditionResolver
{
    /**
     * Checks and evaluates condition
     * @param string $condition condition string (ex: "$a >= 17")
     * @param array $variables set of variables to substitute
     * @return bool
     */
    public function isConditionMet($condition, array $variables)
    {
        preg_match('/^\s*(\$[a-zA-Z\_]+)\s*([\!\<\=\>\%]+)\s*(.+)$/', $condition, $matches);

        if (4 !== count($matches)) {
            throw new \LogicException('Error parsing loop condition');
        }

        $variableValue = (int) Ar::get($variables, $matches[1]);
        $result = (int) $matches[3];
        switch ($matches[2]) {
            case '=':
                return $variableValue == $result;

            case '==':
                return $variableValue == $result;

            case '>':
                return $variableValue > $result;

            case '>=':
                return $variableValue >= $result;

            case '<':
                return $variableValue < $result;

            case '<=':
                return $variableValue <= $result;

            case '<>':
                return $variableValue != $result;

            case '!=':
                return $variableValue != $result;

            case '%':
                return ($variableValue + $result) % $result === 0;

            case '!%':
                return ($variableValue + $result) % $result !== 0;

            default:
                throw new \LogicException('Error parsing comparison operator');
        }
    }
}
