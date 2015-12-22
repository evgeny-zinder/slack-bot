<?php

namespace tests\models;

use slackbot\models\Config;
use slackbot\util\FileLoader;
use Symfony\Component\Yaml\Parser;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $parserMock;
    private $loader;

    public function setUp()
    {
        $this->parserMock = \Mockery::mock('\Symfony\Component\Yaml\Parser');
        $this->parserMock
            ->shouldReceive('parse')
            ->andReturn(
                [
                    'appearance' => [
                        'name' => 'Slack Bot'
                    ],
                    'receive' => [
                        [
                            'name' => 'WebSocket',
                            'type' => 'rtm',
                            'token' => 'xoxb-11771184720-rKdjOqL1Mffno6jJIztouAsa',
                            'enabled' => true
                        ],
                        [
                            'name' => 'Outgoming WebHook',
                            'type' => 'webhook',
                            'token' => 'f5SrHXJr9xlp4BKb6aUnCetH',
                            'enabled' => true
                        ],
                        [
                            'name' => 'Slash Commands',
                            'type' => 'command',
                            'token' => 'khrsHR3MxEOag3StVVl665qe',
                            'enabled' => true
                        ]
                    ],
                    'send' => [
                        [
                            'name' => 'WebSocket',
                            'type' => 'rtm',
                            'token' => 'xoxb-11771184720-rKdjOqL1Mffno6jJIztouAsa',
                            'enabled' => true
                        ],
                        [
                            'name' => 'Incoming WebHook',
                            'type' => 'webhook',
                            'url' => 'https://hooks.slack.com/services/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',
                            'enabled' => true
                        ],
                        [
                            'name' => 'Slack API',
                            'type' => 'api',
                            'token' => 'xoxb-11771184720-rKdjOqL1Mffno6jJIztouAsa',
                            'enabled' => true
                        ]
                    ],
                    'log' => [
                        'path' => 'var/slackbot.log',
                        'level' => 4
                    ],
                    'server' => [
                        'port' => '8888',
                        'pidfile' => 'var/core.pid',
                        'rtmpidfile' => 'var/rtm.pid'
                    ]

                ]
            );

        $this->loader = new FileLoader();
    }

    /** @test */
    public function shouldParseCorrectConfigFile()
    {
        $configFile = <<<CONFIG
log:
    path: var/slackbot.log;
    level: 4
CONFIG;
        $loaderMock = \Mockery::mock('slackbot\util\FileLoader');
        $loaderMock
            ->shouldReceive('load')
            ->andReturn($configFile);

        $config = new Config(
            $this->parserMock,
            $loaderMock
        );
        $config->loadData('tests/data/config-test.yml');
    }

    /** @test */
    public function shouldFailOnIncorrectConfigFile()
    {
        $configFile = <<<CONFIG
xxxxxxxxx
CONFIG;
        $loaderMock = \Mockery::mock('\slackbot\util\FileLoader');
        $loaderMock
            ->shouldReceive('load')
            ->andReturn($configFile);
        $this->setExpectedException('\RuntimeException', 'Error parsing config file');

        $config = new Config(
            new Parser(),
            $loaderMock
        );
        $config->loadData('tests/data/config-test.yml');

    }

    /** @test */
    public function shouldReturnExistingSection()
    {
        $config = new Config($this->parserMock, $this->loader);
        $config->loadData('tests/data/config-test.yml');
        $this->assertEquals(
            [
                'path' => 'var/slackbot.log',
                'level' => 4
            ],
            $config->getSection('log')
        );
    }

    /** @test */
    public function shouldReturnEmptyArrayOnNonExistentSection()
    {
        $config = new Config($this->parserMock, $this->loader);
        $config->loadData('tests/data/config-test.yml');
        $this->assertEquals(
            [],
            $config->getSection('non-existent-section')
        );
    }

    /** @test */
    public function shouldReturnExistingEntry()
    {
        $config = new Config($this->parserMock, $this->loader);
        $config->loadData('tests/data/config-test.yml');
        $this->assertEquals(
            'var/slackbot.log',
            $config->getEntry('log.path')
        );
    }

    /** @test */
    public function shouldReturnFalseOnNonExistentEntry()
    {
        $config = new Config($this->parserMock, $this->loader);
        $config->loadData('tests/data/config-test.yml');
        $this->assertEquals(
            null,
            $config->getEntry('log.non-existent-entry')
        );
    }

    /** @test */
    public function shouldReturnFalseOnNonExistentSectionEntry()
    {
        $config = new Config($this->parserMock, $this->loader);
        $config->loadData('tests/data/config-test.yml');
        $this->assertEquals(
            null,
            $config->getEntry('non-existent-section.path')
        );
    }

}
