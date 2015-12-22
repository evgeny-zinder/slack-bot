<?php

namespace tests\models;

use slackbot\models\ConditionResolver;

class ConditionResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider validConditionsProvider
     */
    public function shouldMeetPreDefinedConditions($condition, $variables, $expected)
    {
        $resolver = new ConditionResolver();

        $result = $resolver->isConditionMet($condition, $variables);

        $this->assertEquals($expected, $result);
    }

    public static function validConditionsProvider()
    {
        return [
            ['$a = 1', ['$a' => 1], true],
            ['$a = 1', ['$b' => 2, '$a' => 1], true],
            ['$a = 1', ['$a' => 2], false],
            ['$a = 1', ['$aa' => 1], false],
            ['$a = 1', [], false],

            ['$a == 1', ['$a' => 1], true],
            ['$a == 1', ['$b' => 2, '$a' => 1], true],
            ['$a == 1', ['$a' => 2], false],
            ['$a == 1', ['$aa' => 1], false],
            ['$a == 1', [], false],

            ['$a > 1', ['$a' => 2], true],
            ['$a > 1', ['$b' => 2, '$a' => 2], true],
            ['$a > 1', ['$a' => 1], false],
            ['$a > 1', ['$a' => 0], false],
            ['$a > -1', ['$aa' => 1], true],
            ['$a > -1', [], true],
            ['$a > 1', ['$aa' => 1], false],
            ['$a > 1', [], false],

            ['$a < 1', ['$a' => 0], true],
            ['$a < 1', ['$b' => 2, '$a' => 0], true],
            ['$a < 1', ['$a' => 1], false],
            ['$a < 1', ['$a' => 2], false],
            ['$a < 1', ['$aa' => 0], true],
            ['$a < 1', [], true],
            ['$a < -1', ['$aa' => 0], false],
            ['$a < -1', [], false],

            ['$a >= 1', ['$a' => 2], true],
            ['$a >= 1', ['$b' => 2, '$a' => 2], true],
            ['$a >= 1', ['$a' => 1], true],
            ['$a >= 1', ['$a' => 0], false],
            ['$a >= -1', ['$aa' => 1], true],
            ['$a >= -1', [], true],
            ['$a >= 1', ['$aa' => 1], false],
            ['$a >= 1', [], false],

            ['$a <= 1', ['$a' => 0], true],
            ['$a <= 1', ['$b' => 2, '$a' => 0], true],
            ['$a <= 1', ['$a' => 1], true],
            ['$a <= 1', ['$a' => 2], false],
            ['$a <= 1', ['$aa' => 0], true],
            ['$a <= 1', [], true],
            ['$a <= -1', ['$aa' => 0], false],
            ['$a <= -1', [], false],

            ['$a <> 1', ['$a' => 0], true],
            ['$a <> 1', ['$b' => 2, '$a' => 0], true],
            ['$a <> 1', ['$a' => 1], false],
            ['$a <> 1', ['$aa' => 0], true],
            ['$a <> 0', ['$aa' => 0], false],
            ['$a <> 1', [], true],
            ['$a <> 0', [], false],

            ['$a != 1', ['$a' => 0], true],
            ['$a != 1', ['$b' => 2, '$a' => 0], true],
            ['$a != 1', ['$a' => 1], false],
            ['$a != 1', ['$aa' => 0], true],
            ['$a != 0', ['$aa' => 0], false],
            ['$a != 1', [], true],
            ['$a != 0', [], false],

            ['$a % 2', ['$a' => 4], true],
            ['$a % 2', ['$b' => 3, '$a' => 4], true],
            ['$a % 2', ['$a' => 0], true],
            ['$a % 2', ['$a' => 3], false],
            ['$a % 2', ['$a' => 1], false],
            ['$a % 1', ['$a' => 1], true],

            ['$a !% 2', ['$a' => 4], false],
            ['$a !% 2', ['$b' => 3, '$a' => 4], false],
            ['$a !% 2', ['$a' => 0], false],
            ['$a !% 2', ['$a' => 3], true],
            ['$a !% 2', ['$a' => 1], true],
            ['$a !% 1', ['$a' => 1], false],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConditionsProvider
     * @xexpectedException Exception
     */
    public function shouldThrowExceptionOnInvalidCondition($condition, $variables, $exceptionName)
    {
        $this->setExpectedException($exceptionName);
        $resolver = new ConditionResolver();
        $resolver->isConditionMet($condition, $variables);
    }

    public function invalidConditionsProvider()
    {
        return [
            ['some invalid condition', [], 'LogicException'],
            ['$a + - 1', [], 'LogicException'],
            ['$a +++ 1', [], 'LogicException'],
        ];
    }
}
