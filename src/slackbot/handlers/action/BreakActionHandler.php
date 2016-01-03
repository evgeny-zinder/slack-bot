<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;

/**
 * Class BreakActionHandler
 * @package slackbot\handlers\action
 */
class BreakActionHandler extends BaseActionHandler
{
    public function __construct() {}

    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return 'break' === $dto->getAction();
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        Variables::set('flowcontrol.break', true);
    }
}
