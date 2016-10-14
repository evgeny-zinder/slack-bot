<?php

namespace slackbot\handlers\command;

use eznio\ar\Ar;
use eznio\tabler\renderers\MysqlStyleRenderer;
use eznio\tabler\Tabler;
use slackbot\helpers\TimeAgoHelper;
use slackbot\models\Registry;
use slackbot\models\SlackFacade;
use Pimple\Container;

class StatusCommandHandler extends BaseCommandHandler
{
    public function getName()
    {
        return 'status';
    }

    public function getAcl()
    {
        return CommandHandlerInterface::ACL_ANY;
    }

    public function processCommand(array $args, $channel)
    {
        /** @var Container $container */
        $container = Registry::get('container');

        /** @var SlackFacade $slackFacade */
        $slackFacade = $container['slack_facade'];

        $currentUserId = $slackFacade->getMyId();

        $this->postMessage($channel, sprintf(
            'I\'m `@%s` (ID `%s`), started %s',
            $slackFacade->getMyName(),
            $currentUserId,
            (new TimeAgoHelper())->format($container['started'])
        ));

        $channels = Ar::map(
            $slackFacade->getUserChannels($currentUserId),
            function($channel) {
                return [Ar::get($channel, 'id') => [
                    'name' => Ar::get($channel, 'name'),
                    'members' => count(Ar::get($channel, 'members'))
                ]];
            }
        );
        $groups = Ar::map(
            $slackFacade->getUserGroups($currentUserId),
            function($group) {
                return [Ar::get($group, 'id') => [
                    'name' => Ar::get($group, 'name'),
                    'members' => count(Ar::get($group, 'members'))
                ]];
            }
        );

        $channelsOutput = (new Tabler())
            ->setRenderer(new MysqlStyleRenderer())
            ->setHeaders(['name' => 'Channel', 'members' => '# of members'])
            ->setData($channels)
            ->render();

        $groupsOutput = (new Tabler())
            ->setRenderer(new MysqlStyleRenderer())
            ->setHeaders(['name' => 'Group', 'members' => '# of members'])
            ->setData($groups)
            ->render();

        $this->postMessage($channel, sprintf(
            'My channels (%d): ```%s```',
            count($channels),
            $channelsOutput
        ));

        $this->postMessage($channel, sprintf(
            'My groups (%d): ```%s```',
            count($groups),
            $groupsOutput
        ));
    }
}