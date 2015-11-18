<?php

namespace tests\models;

use slackbot\models\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldStoreDataInRegistry()
    {
        Registry::set('test', '123');
        $this->assertEquals(
            '123',
            Registry::get('test')
        );
    }

    /** @test */
    public function shouldReturnNullOnNonExistentKey()
    {
        $this->assertEquals(
            null,
            Registry::get('test1')
        );
    }
}
