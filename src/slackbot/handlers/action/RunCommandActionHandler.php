<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\Util;
use slackbot\models\Registry;
use slackbot\handlers\command\CommandHandlerInterface;

/**
 * Class RunCommandActionHandler
 * @package slackbot\handlers\action
 */
class RunCommandActionHandler extends BaseActionHandler
{
    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return Util::arrayGet($dto->getData(), 'action') === 'run_command';
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        $container = Registry::get('container');
        $commandName = Util::arrayGet($dto->getData(), 'command');

        /** @var CommandHandlerInterface $command */
        $command = $container['command_' . $commandName];
        if (null === $command) {
            return;
        }
        $command->processCommand(
            Util::arrayGet($dto->getData(), 'args'),
            Util::arrayGet($dto->getData(), 'channel')
        );
    }
}
