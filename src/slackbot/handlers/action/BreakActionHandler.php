<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;
use slackbot\Util;

class BreakActionHandler extends BaseActionHandler
{
    public function __construct() {}

    /**
     * @param ActionDto $dto
     * @return boolean
     */
    public function canProcessAction(ActionDto $dto)
    {
        return Util::arrayGet($dto->getData(), 'action') === 'break';
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
