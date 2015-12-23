<?php

namespace tests\unit\models;

use slackbot\models\SlackApi;

class SlackApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider simpleRequestsProvider
     */
    public function shouldProcessSimpleSlackRequests($urlPart, $methodName, $methodArgs)
    {
        $token = 'token';
        $curlPostFields = array_merge($methodArgs, ['token' => $token]);
        $curlRequestMock = \Mockery::mock('\slackbot\util\CurlRequest');
        $curlRequestMock
            ->shouldReceive('getCurlResult')
            ->withArgs($methodArgs)
            ->withArgs([
                'https://slack.com/api/' . $urlPart,
                [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $curlPostFields
                ]
            ])
            ->once();

        $slackApi = new SlackApi($curlRequestMock);
        $slackApi->setToken($token);
        call_user_func_array([$slackApi, $methodName], array_values($methodArgs));
    }

    public static function simpleRequestsProvider()
    {
        return [
            [
                'channels.list',
                'channelsList',
                []
            ],
            [
                'users.list',
                'usersList',
                []
            ],
            [
                'groups.list',
                'groupsList',
                []
            ],
            [
                'channels.info',
                'channelsInfo',
                ['channel' => '#general']
            ],
            [
                'groups.info',
                'groupsInfo',
                ['group' => 'private-group']
            ],
            [
                'im.open',
                'imOpen',
                ['user' => '@user']
            ],

        ];
    }

    /** @test */
    public function shouldSendMessagesToSlack()
    {
        $token = 'token';
        $curlRequestMock = \Mockery::mock('\slackbot\util\CurlRequest');
        $curlRequestMock
            ->shouldReceive('getCurlResult')
            ->withArgs([
                'https://slack.com/api/chat.postMessage',
                [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => [
                        'channel' => '#general',
                        'text' => 'this is a test',
                        'token' => $token
                    ]
                ]
            ])
            ->once();

        $slackApi = new SlackApi($curlRequestMock);
        $slackApi->setToken($token);
        $slackApi->chatPostMessage('#general', 'this is a test');
    }

    /** @test */
    public function shouldReturnToken()
    {
        $token = 'token';
        $curlRequestMock = \Mockery::mock('\slackbot\util\CurlRequest');

        $slackApi = new SlackApi($curlRequestMock);
        $slackApi->setToken($token);

        $this->assertEquals($token, $slackApi->getToken());
    }
}
