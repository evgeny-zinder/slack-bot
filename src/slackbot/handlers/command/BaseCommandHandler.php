<?php

namespace slackbot\handlers\command;

use slackbot\models\Registry;

/**
 * Class BaseCommandHandler
 * @package slackbot\handlers\command
 */
abstract class BaseCommandHandler implements CommandHandlerInterface
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param array $args Command arguments passed from Slack
     * @param string $channel Channel ID to send response to
     * @return null
     */
    abstract public function processCommand(array $args, $channel);

    /**
     * @param array $args Command arguments passed from Slack
     * @param string $channel Channel ID to send response to
     * @return bool
     */
    public function canProcessCommand(array $args, $channel)
    {
        return true;
    }

    /**
     * Get ACL for this command
     * ACL can be int (ACL_ANY / ACL_ADMIN), or array of strings - channel/group/user IDs
     * @return int|array
     */
    public function getAcl()
    {
        return CommandHandlerInterface::ACL_ANY;
    }

    /**
     * @return \Pimple\Container
     */
    protected function getContainer()
    {
        return Registry::get('container');
    }

    /**
     * @param string $channel Channel to send message to
     * @param string $message Message to send
     * @param array $options Other Slack message formatting options
     * @return null
     */
    protected function postMessage($channel, $message, $options = [])
    {
        $container = $this->getContainer();
        $container['slack_facade']->getSlackApi()->chatPostMessage($channel, $message, $options);
    }
}
