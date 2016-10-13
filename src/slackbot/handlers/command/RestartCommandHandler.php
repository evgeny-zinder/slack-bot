<?php

namespace slackbot\handlers\command;

use slackbot\handlers\command\CommandHandlerInterface;
use slackbot\logging\Logger;
use slackbot\models\Registry;
use slackbot\models\SlackFacade;
use slackbot\Util;
use slackbot\util\Posix;

class RestartCommandHandler extends BaseCommandHandler
{
    public function getName()
    {
        return 'restart';
    }

    public function getAcl()
    {
        return CommandHandlerInterface::ACL_ADMIN;
    }

    public function processCommand(array $args, $channel)
    {
        Logger::get()->warning('Server restarted by %s', $this->getCallerName());
        Posix::execute(['sudo service supervisord restart']);
    }
}