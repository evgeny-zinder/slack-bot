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
        // one can also use CommandHandlerInterface::ACL_ANY;
        return [
            '#general'
        ];
    }

    public function processCommand(array $args, $channel)
    {
        $this->postMessage($channel, 'You\'ve asked for: ' . json_encode($args));
    }
}
