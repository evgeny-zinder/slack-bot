<?php

namespace tests\unit\models;

use slackbot\models\SlackApi;

class SlackApiTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldProcessSimpleSlackRequests()
    {
        $token = 'token';
        $curlRequestMock = \Mockery::mock('\slackbot\util\CurlRequest');
        $curlRequestMock
            ->shouldReceive('getCurlResult')
            ->withArgs([
                'https://slack.com/api/channels.list',
                [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => [
                        'token' => $token
                    ]
                ]
            ])
            ->once();

        $slackApi = new SlackApi($curlRequestMock);
        $slackApi->setToken($token);
        $slackApi->channelsList();
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

}
