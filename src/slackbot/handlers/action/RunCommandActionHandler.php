<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
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
        return 'run_command' === $dto->get('action');
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        $container = Registry::get('container');
        $commandName = $dto->get('command');

        /** @var CommandHandlerInterface $command */
        $command = $container['command_' . $commandName];
        if (null === $command) {
            return;
        }
        $command->processCommand(
            $dto->get('args'),
            $dto->get('channel')
        );
    }
}
