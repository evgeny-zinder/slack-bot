<?php

namespace slackbot\handlers\command;

interface CommandHandlerInterface
{
    const ACL_ANY = -1;

    public function getName();
    public function getAcl();
    public function processCommand(array $args, $channel);
}
