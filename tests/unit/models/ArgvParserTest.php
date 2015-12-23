<?php

namespace tests\models;

use slackbot\models\ArgvParser;

class ArgvParserTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldReturnSingleParameter()
    {
        $args = [
            '--a=b',
            '--c=d',
            '--e=fffff',
        ];
        $parser = new ArgvParser($args);
        $parser->parse();

        $this->assertEquals('fffff', $parser->get('e'));
    }

    /** @test */
    public function shouldNotReturnInvalidSingleParameter()
    {
        $args = [
            '--a=b',
            '--c=d',
            '--e=fffff',
        ];
        $parser = new ArgvParser($args);
        $parser->parse();

        $this->assertEquals(null, $parser->get('z'));
    }

    /** @test */
    public function shouldReturnAllParameters()
    {
        $args = [
            '--a=b',
            '--c=d',
            '--e=fffff',
        ];
        $parser = new ArgvParser($args);
        $parser->parse();

        $this->assertEquals(
            [
                'a' => 'b',
                'c' => 'd',
                'e' => 'fffff'
            ],
            $parser->all()
        );

    }

    /** @test */
    public function shouldNotParseInvalidParams()
    {
        $args = [
            '--=b',
            '--cd',
            '--e==fffff',
        ];
        $parser = new ArgvParser($args);
        $parser->parse();

        $this->assertEquals([], $parser->all());

    }

    /** @test */
    public function shouldNotParseEmptyParams()
    {
        $parser = new ArgvParser([]);
        $parser->parse();

        $this->assertEquals([], $parser->all());

    }

    /** @test */
    public function shouldReplaceArgvOnParseCall()
    {
        $parser = new ArgvParser([]);
        $parser->parse(['--a=b']);

        $this->assertEquals(['a' => 'b'], $parser->all());

    }

}
