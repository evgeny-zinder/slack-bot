<?php

namespace slackbot\handlers\command;

/**
 * Interface CommandHandlerInterface
 * @package slackbot\handlers\command
 */
interface CommandHandlerInterface
{
    // Anybody is allowed in
    const ACL_ANY = -1;

    // Only server admins, set in 'admins' config section, are allowed
    const ACL_ADMIN = -2;

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int|array
     */
    public function getAcl();

    /**
     * @param array $args Command arguments passed from Slack
     * @param string $channel Channel ID to send response to
     * @return bool
     */
    public function canProcessCommand(array $args, $channel);

    /**
     * @param array $args Command arguments passed from Slack
     * @param string $channel Channel ID to send response to
     * @return null
     */
    public function processCommand(array $args, $channel);
}
