<?php

namespace slackbot\handlers\command;

use slackbot\logging\Logger;
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