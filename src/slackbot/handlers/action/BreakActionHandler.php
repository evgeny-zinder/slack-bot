<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;
use slackbot\Util;

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
        return 'break' === Util::arrayGet($dto->getData(), 'action');
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
