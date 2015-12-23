<?php

namespace tests\models;

use slackbot\models\VariablesPlacer;

class VariablesPlacerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldParseSignleVariable()
    {
        $placer = new VariablesPlacer();
        $placer->setText('This is a %var%');
        $placer->setVars(['var' => 'test']);

        $result = $placer->place();

        $this->assertEquals('This is a test', $result);
    }

    /** @test */
    public function shouldParseMultipleVariables()
    {
        $placer = new VariablesPlacer();
        $placer->setText('This %var1% a %var2%');
        $placer->setVars(
            [
                'var1' => 'is',
                'var2' => 'test'
            ]
        );

        $result = $placer->place();

        $this->assertEquals('This is a test', $result);
    }

    /** @test */
    public function shouldParseSignleVariableMultipleTimes()
    {
        $placer = new VariablesPlacer();
        $placer->setText('The %var% is a %var% for a %var%');
        $placer->setVars(
            [
                'var' => 'test'
            ]
        );

        $result = $placer->place();

        $this->assertEquals('The test is a test for a test', $result);
    }

    /** @test */
    public function shouldParseMultipleVariablesMultipleTimes()
    {
        $placer = new VariablesPlacer();
        $placer->setText('The %var1% is %var2% %var1% for %var2% %var1%');
        $placer->setVars(
            [
                'var1' => 'test',
                'var2' => 'a'
            ]
        );

        $result = $placer->place();

        $this->assertEquals('The test is a test for a test', $result);
    }

    /** @test */
    public function shouldNotParseInvalidVariableNames()
    {
        $placer = new VariablesPlacer();
        $placer->setText('This % is a %var');
        $placer->setVars(
            [
                'var' => 'test',
                'is' => 'none'
            ]
        );

        $result = $placer->place();

        $this->assertEquals('This % is a %var', $result);

    }

}
