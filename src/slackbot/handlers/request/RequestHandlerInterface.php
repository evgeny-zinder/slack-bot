<?php

namespace slackbot\handlers\request;

use slackbot\dto\RequestDto;

/**
 * Interface RequestHandlerInterface
 * @package slackbot\handlers\request
 */
interface RequestHandlerInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return bool
     */
    public function shouldReceiveOwnMessages();

    /**
     * @param RequestDto $request
     * @return bool
     */
    public function canProcessRequest(RequestDto $request);

    /**
     * @param RequestDto $request
     * @return null
     */
    public function processRequest(RequestDto $request);
}
