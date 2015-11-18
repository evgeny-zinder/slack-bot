<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;

interface ActionHandlerInterface
{
    public function canProcessAction(ActionDto $dto);
    public function processAction(ActionDto $dto);
}
