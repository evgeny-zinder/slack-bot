<?php

namespace slackbot\handlers\request;

use slackbot\dto\RequestDto;

/**
 * Interface RequestHandlerInterface
 * @package slackbot\handlers\request
 */
interface RequestHandlerInterface
{
    const STOP_PROCESSING = -1;

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
     * @param array $params
     * @return null
     */
    public function processRequest(RequestDto $request, array $params);
}
