<?php

namespace slackbot\handlers\command;

class TestCommandHandler extends BaseCommandHandler
{
    public function getName()
    {
        return 'test';
    }

    public function getAcl()
    {
        // one can also use CommandHandlerInterface::ACL_ANY, CommandHandlerInterface::ACL_ADMIN
        // or user/group/channel name
        return [
            CommandHandlerInterface::ACL_ANY
        ];
    }

    public function canProcessCommand(array $args, $channel) {
        $config = $this->getContainer()['config'];
        return $config->getEntry('server.id') == 'debug';
    }

    public function processCommand(array $args, $channel)
    {
        $this->postMessage($channel, 'You\'ve asked for: ' . json_encode($args));
    }
}
