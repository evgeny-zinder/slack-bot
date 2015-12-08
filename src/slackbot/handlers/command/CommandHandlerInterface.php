<?php

namespace slackbot\handlers\command;

interface CommandHandlerInterface
{
    const ACL_ANY = -1;
    const ACL_ADMIN = -2;

    public function getName();
    public function getAcl();
    public function canProcessCommand(array $args, $channel);
    public function processCommand(array $args, $channel);
}
