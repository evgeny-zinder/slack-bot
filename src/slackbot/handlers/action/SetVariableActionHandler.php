<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;
use slackbot\Util;

/**
 * Class SetVariableActionHandler
 * @package slackbot\handlers\action
 */
class SetVariableActionHandler extends BaseActionHandler
{
    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return
            'set_variable' === Util::arrayGet($dto->getData(), 'action')
            || 'set' === Util::arrayGet($dto->getData(), 'action');
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        $name = Util::arrayGet($dto->getData(), 'name');
        $value = Util::arrayGet($dto->getData(), 'value');
        switch ((string) $value) {
            case 'increment':
                Variables::set(
                    $name,
                    Variables::get($name) + 1
                );
                break;

            case 'decrement':
                Variables::set(
                    $name,
                    Variables::get($name) - 1
                );
                break;

            default:
                Variables::set(
                    $name,
                    $value
                );
        }
    }

}
