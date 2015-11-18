<?php

namespace slackbot\handlers\request;

use slackbot\dto\RequestDto;

interface RequestHandlerInterface
{
    public function getId();
    public function shouldReceiveOwnMessages();
    public function canProcessRequest(RequestDto $request);
    public function processRequest(RequestDto $request);
}
