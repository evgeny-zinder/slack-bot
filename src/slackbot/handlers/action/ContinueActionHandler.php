<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;
use slackbot\Util;

/**
 * Class ContinueActionHandler
 * @package slackbot\handlers\action
 */
class ContinueActionHandler extends BaseActionHandler
{
    public function __construct() {}

    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return  'continue' === $dto->getAction();
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        Variables::set('flowcontrol.continue', true);
    }
}
