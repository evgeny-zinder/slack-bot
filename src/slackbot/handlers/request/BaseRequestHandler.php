<?php

namespace slackbot\handlers\request;

use slackbot\dto\RequestDto;
use slackbot\models\SlackFacade;

abstract class BaseRequestHandler implements RequestHandlerInterface
{
    /** @var SlackFacade */
    protected $slackFacade;

    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    public function getId()
    {
        return __CLASS__;
    }

    /**
     * @param RequestDto $dto
     * @return boolean
     */
    abstract public function canProcessRequest(RequestDto $dto);

    /**
     * @param RequestDto $dto
     * @return null
     */
    abstract public function processRequest(RequestDto $dto);

    abstract public function shouldReceiveOwnMessages();
}
