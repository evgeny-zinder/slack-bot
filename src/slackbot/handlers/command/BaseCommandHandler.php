<?php

namespace slackbot\handlers\command;

use slackbot\models\Registry;

abstract class BaseCommandHandler implements CommandHandlerInterface
{
    abstract public function getName();
    abstract public function processCommand(array $args, $channel);

    public function getAcl()
    {
        return CommandHandlerInterface::ACL_ANY;
    }

    protected function getContainer()
    {
        return Registry::get('container');
    }

    protected function postMessage($channel, $message, $options = [])
    {
        $container = $this->getContainer();
        $container['slack_facade']->getSlackApi()->chatPostMessage($channel, $message, $options);
    }
}
