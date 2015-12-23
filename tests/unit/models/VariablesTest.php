<?php

namespace tests\models;

use slackbot\models\Variables;

class VariablesTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldStoreVariableValue()
    {
        Variables::set('test', '123');
        $this->assertEquals(
            '123',
            Variables::get('test')
        );
    }

    /** @test */
    public function shouldReturnNullOnNonExistentKey()
    {
        $this->assertEquals(
            null,
            Variables::get('test1')
        );
    }

    /** @test */
    public function shouldReturnAllVariableValues()
    {
        Variables::clear();
        Variables::set('test1', '123');
        Variables::set('test2', '456');
        $this->assertEquals(
            [
                'test1' => '123',
                'test2' => '456'
            ],
            Variables::all()
        );
    }

    /** @test */
    public function shouldRemoveVariableValues()
    {
        Variables::clear();
        Variables::set('test1', '123');
        Variables::remove('test1');
        $this->assertEquals([], Variables::all());
    }


    /** @test */
    public function shouldClearAllVariableValues()
    {
        Variables::clear();
        Variables::set('test1', '123');
        Variables::set('test2', '456');
        Variables::clear();
        $this->assertEquals([], Variables::all());
    }
}
