<?php

namespace slackbot\handlers\request;

use slackbot\dto\RequestDto;
use slackbot\models\SlackFacade;

/**
 * Class BaseRequestHandler
 * @package slackbot\handlers\request
 */
abstract class BaseRequestHandler implements RequestHandlerInterface
{
    /** @var SlackFacade */
    protected $slackFacade;

    /**
     * BaseRequestHandler constructor.
     * @param SlackFacade $slackFacade
     */
    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    /**
     * @return string
     */
    public function getId()
    {
        $data = explode('\\', static::class);
        return array_pop($data);
    }

    /**
     * Decides if request handler should process message
     * @param RequestDto $dto
     * @return bool
     */
    abstract public function canProcessRequest(RequestDto $dto);

    /**
     * Processes message
     * @param RequestDto $dto
     * @return null
     */
    abstract public function processRequest(RequestDto $dto, array $params);

    /**
     * Decides if bot's own messages should be passed to request handler
     * @return bool
     */
    abstract public function shouldReceiveOwnMessages();
}
