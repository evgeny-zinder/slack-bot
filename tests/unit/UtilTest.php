<?php

namespace tests;

use slackbot\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider arrayGetDataProvider
     * @param array $array
     * @param mixed $path
     * @param mixed $sampler
     */
    public function shouldReturnRightArrayGetValues($array, $path, $sampler)
    {
        $result = Util::arrayGet($array, $path);

        $this->assertEquals($sampler, $result);
    }

    public static function arrayGetDataProvider()
    {
        return [
            [
                ['a' => 'b', 'c' => 'd', 'e' => 'f'],
                'c',
                'd'
            ],
            [
                ['a' => 'b', 'c' => 'd', 'e' => 'f'],
                'g',
                null
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                'a.b.c',
                'd'
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                'a.b',
                ['c' => 'd']
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                'a',
                ['b' => ['c' => 'd']]
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                '',
                null
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                '.b.c',
                null
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                'a.c',
                null
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                null,
                null
            ],
            [
                ['a' => ['b' => ['c' => 'd']]],
                'totallyWrongPath',
                null
            ],
            [
                null,
                'a',
                null
            ]
        ];
    }
}
