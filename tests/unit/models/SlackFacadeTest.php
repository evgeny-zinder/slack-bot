<?php

namespace tests\unit\models;

use slackbot\models\SlackApi;
use slackbot\models\SlackFacade;
use slackbot\util\CurlRequest;

class SlackFacadeTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldReturnUserInfoForValidUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserByName('some-user');

        $this->assertEquals(
            [
                'id' => 'U00000002',
                'name' => 'some-user',
                'deleted' => false,
                'is_admin' => false,
                'is_owner' => false,
            ],
            $result
        );
    }

    /** @test */
    public function shouldReturnEmptyArrayForInvalidUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserByName('invalid-user');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldReturnSlackApiInstance()
    {
        $slackApi = new SlackApi(new CurlRequest());

        $slackFacade = new SlackFacade($slackApi);

        $this->assertEquals(
            'slackbot\models\SlackApi',
            get_class($slackFacade->getSlackApi())
        );
    }

    /** @test */
    public function shouldReturnUserIdForValidUserNameWithoutOpeningImChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserIdByName('some-user', false);

        $this->assertEquals('U00000002', $result);
    }

    /** @test */
    public function shouldReturnNullForInvalidUserNameWithoutOpeningImChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserIdByName('invalid-user', false);

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldReturnImIdForValidUserNameWithOpeningImChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserIdByName('some-user', true);

        $this->assertEquals('M00000123', $result);
    }

    /** @test */
    public function shouldReturnNullForInvalidUserNameWithOpeningImChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getUserIdByName('invalid-user', true);

        $this->assertEquals(null, $result);
    }

    // getChannelByName
    /** @test */
    public function shouldGetChannelByRightName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelByName('random');

        $this->assertEquals(
            [
                'id' => 'C00000002',
                'name' => 'random',
            ],
            $result
        );
    }

    /** @test */
    public function shouldNotGetChannelByWrongName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelByName('non-existent-channel');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetChannelByEmptyName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelByName(null);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetChannelByNonStringName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelByName(new \StdClass());

        $this->assertEquals([], $result);
    }

    // getChannelIdByName
    /** @test */
    public function shouldGetChannelIdByRightName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelIdByName('random');

        $this->assertEquals('C00000002', $result);
    }

    /** @test */
    public function shouldNotGetChannelIdByWrongName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelIdByName('non-existent-channel');

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetChannelIdByEmptyName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelIdByName(null);

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetChannelIdByNonStringName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getChannelIdByName(new \StdClass());

        $this->assertEquals(null, $result);
    }

    // getGroupByName
    /** @test */
    public function shouldGetGroupByRightName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupByName('another-group');

        $this->assertEquals(
            [
                'id' => 'G00000002',
                'name' => 'another-group',
            ],
            $result
        );
    }

    /** @test */
    public function shouldNotGetGroupByWrongName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupByName('non-existent-group');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetGroupByEmptyName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupByName(null);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetGroupByNonStringName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupByName(new \StdClass());

        $this->assertEquals([], $result);
    }

    // getGroupIdByName
    /** @test */
    public function shouldGetGroupIdByRightName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupIdByName('another-group');

        $this->assertEquals('G00000002', $result);
    }

    /** @test */
    public function shouldNotGetGroupIdByWrongName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupIdByName('non-existent-group');

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetGroupIdByEmptyName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupIdByName(null);

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetGroupIdByNonStringName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getGroupIdByName(new \StdClass());

        $this->assertEquals(null, $result);

    }

    // getRecipientIdByName
    /** @test */
    public function shouldGetRecipientIdByShortUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]            );
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);


        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('@some-user');

        $this->assertEquals('M00000123', $result);
    }

    /** @test */
    public function shouldGetRecipientIdByLongUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('<@U00000002>');

        $this->assertEquals('U00000002', $result);
    }

    /** @test */
    public function shouldGetRecipientIdByShortChannelName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('#random');

        $this->assertEquals('C00000002', $result);
    }

    /** @test */
    public function shouldGetRecipientIdByLongChannelName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('<@C00000002>');

        $this->assertEquals('C00000002', $result);
    }

    /** @test */
    public function shouldGetRecipientIdByShortGroupName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('another-group');

        $this->assertEquals('G00000002', $result);
    }

    /** @test */
    public function shouldGetRecipientIdByLongGroupName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('<@G00000002>');

        $this->assertEquals('G00000002', $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdByWrongUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]            );
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);


        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('@non-existent-user');

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdByWrongChannelName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('#non-existent-channel');

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdByWrongGroupName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName('non-existent-group');

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdByEmptyName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName(null);

        $this->assertEquals(null, $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdByArrayInsteadOfName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdByName(new \StdClass());

        $this->assertEquals(null, $result);
    }

    // getRecipientIdsByNames
    /** @test */
    public function shouldGetRecipientIdsBySingleUserName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['@some-user']);

        $this->assertEquals(['M00000123'], $result);
    }

    /** @test */
    public function shouldGetRecipientIdsBySingleGroupName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['another-group']);

        $this->assertEquals(['G00000002'], $result);
    }

    /** @test */
    public function shouldGetRecipientIdsBySingleChannelName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['#random']);

        $this->assertEquals(['C00000002'], $result);
    }

    /** @test */
    public function shouldGetRecipientIdsByMultipleValidNames()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['@some-user', '#random', 'some-group', '#general']);

        $this->assertEquals(['M00000123', 'C00000002', 'G00000001', 'C00000001'], $result);
    }

    /** @test */
    public function shouldGetRecipientIdsByMultipleNamesWithSomeInvalid()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['@no-user', '#random', 'no-group', '#general']);

        $this->assertEquals(['C00000002', 'C00000001'], $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdsByMultipleNamesWithAllInvalid()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames(['@no-user', '#no-random', 'no-group', '#no-general']);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetRecipientIdsByEmptyNamesList()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);
        $slackApiMock
            ->shouldReceive('imOpen')
            ->withArgs(['U00000002'])
            ->andReturn([
                'channel' => [
                    'id' => 'M00000123'
                ]
            ]);
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientIdsByNames([]);

        $this->assertEquals([], $result);
    }

    // multiSendMessage
    /** @test */
    public function shouldMultiSendMessageToSingleRecipient()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000001', 'Test message', []]);

        $slackFacade = new SlackFacade($slackApiMock);
        $slackFacade->multiSendMessage(['U00000001'], 'Test message');
    }

    /** @test */
    public function shouldMultiSendMessageToMultipleRecipients()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000001', 'Test message', []]);
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000002', 'Test message', []]);
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000003', 'Test message', []]);

        $slackFacade = new SlackFacade($slackApiMock);
        $slackFacade->multiSendMessage(['U00000001', 'U00000003', 'U00000002'], 'Test message');
    }

    /** @test */
    public function shouldMultiSendMessageToSingleChannelWithOptionsBypas()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000001', 'Test message', ['as_user' => true]]);

        $slackFacade = new SlackFacade($slackApiMock);
        $slackFacade->multiSendMessage(['U00000001'], 'Test message', ['as_user' => true]);
    }

    /** @test */
    public function shouldMultiSendMessageToMultipleRecipientsWitnOptionsBypass()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000001', 'Test message', ['as_user' => true]]);
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000002', 'Test message', ['as_user' => true]]);
        $slackApiMock
            ->shouldReceive('chatPostMessage')
            ->withArgs(['U00000003', 'Test message', ['as_user' => true]]);

        $slackFacade = new SlackFacade($slackApiMock);
        $slackFacade->multiSendMessage(['U00000001', 'U00000003', 'U00000002'], 'Test message', ['as_user' => true]);
    }

    // getRecipientUsersIds
    /** @test */
    public function shouldGetRecipientUsersIdsForSingleUserChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('channelsInfo')
            ->andReturn(
                [
                    'channel' => [
                        'id' => 'C00000001',
                        'name' => 'general',
                        'members' => [
                            'U00000001'
                        ]
                    ]
                ]
            );
        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('#general');

        $this->assertEquals(['U00000001'], $result);
    }

    /** @test */
    public function shouldGetRecipientUsersIdsForSingleUserGroup()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsInfo')
            ->andReturn(
                [
                    'group' => [
                        'id' => 'G00000001',
                        'name' => 'some-group',
                        'members' => [
                            'U00000001'
                        ]
                    ]
                ]
            );
        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('some-group');

        $this->assertEquals(['U00000001'], $result);
    }

    /** @test */
    public function shouldGetRecipientUsersIdsForSingleUser()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('@user');

        $this->assertEquals(['U00000001'], $result);
    }

    /** @test */
    public function shouldGetRecipientUsersIdsForMultiUserChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('channelsInfo')
            ->andReturn(
                [
                    'channel' => [
                        'id' => 'C00000001',
                        'name' => 'general',
                        'members' => [
                            'U00000001',
                            'U00000002',
                            'U00000003'
                        ]
                    ]
                ]
            );
        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('#general');

        $this->assertEquals(['U00000001', 'U00000002', 'U00000003'], $result);
    }

    /** @test */
    public function shouldGetRecipientUsersIdsForMultiUserGroup()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );
        $slackApiMock
            ->shouldReceive('groupsInfo')
            ->andReturn(
                [
                    'group' => [
                        'id' => 'G00000001',
                        'name' => 'some-group',
                        'members' => [
                            'U00000001',
                            'U00000002',
                            'U00000003'
                        ]
                    ]
                ]
            );
        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('some-group');

        $this->assertEquals(['U00000001', 'U00000002', 'U00000003'], $result);
    }

    /** @test */
    public function shouldNotGetRecipientUsersIdsForNonExistentChannel()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('channelsList')
            ->andReturn(
                [
                    'channels' => [
                        [
                            'id' => 'C00000001',
                            'name' => 'general',
                        ],
                        [
                            'id' => 'C00000002',
                            'name' => 'random',
                        ],
                        [
                            'id' => 'C00000003',
                            'name' => 'some-other-channel',
                        ]
                    ]
                ]
            );
        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('#no-channel');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetRecipientUsersIdsForNonExistentGroup()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('groupsList')
            ->andReturn(
                [
                    'groups' => [
                        [
                            'id' => 'G00000001',
                            'name' => 'some-group',
                        ],
                        [
                            'id' => 'G00000002',
                            'name' => 'another-group',
                        ],
                        [
                            'id' => 'G00000003',
                            'name' => 'yet-another-group',
                        ]
                    ]
                ]
            );

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('no-group');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetRecipientUsersIdsForNonExistentUser()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');
        $slackApiMock
            ->shouldReceive('usersList')
            ->andReturn(
                [
                    'members' => [
                        [
                            'id' => 'U00000001',
                            'name' => 'user',
                            'deleted' => false,
                            'is_admin' => true,
                            'is_owner' => true,
                        ],
                        [
                            'id' => 'U00000002',
                            'name' => 'some-user',
                            'deleted' => false,
                            'is_admin' => false,
                            'is_owner' => false,
                        ],
                        [
                            'id' => 'U00000003',
                            'name' => 'banned-user',
                            'deleted' => true,
                            'is_admin' => false,
                            'is_owner' => false,
                        ]
                    ]
                ]);

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds('@nouser');

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetRecipientUsersIdsForNullEntityName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds(null);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function shouldNotGetRecipientUsersIdsForArrayEntityName()
    {
        $slackApiMock = \Mockery::mock('\slackbot\models\SlackApi');

        $slackFacade = new SlackFacade($slackApiMock);
        $result = $slackFacade->getRecipientUsersIds(['@user', '#general']);

        $this->assertEquals([], $result);
    }
}
