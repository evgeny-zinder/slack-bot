<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;

/**
 * Interface ActionHandlerInterface
 * @package slackbot\handlers\action
 */
interface ActionHandlerInterface
{
    public function canProcessAction(ActionDto $dto);
    public function processAction(ActionDto $dto);
}
