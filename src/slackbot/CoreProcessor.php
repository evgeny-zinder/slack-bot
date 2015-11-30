<?php

namespace slackbot;

use slackbot\dto\ActionDto;
use slackbot\dto\RequestDto;
use slackbot\handlers\action\ActionHandlerInterface;
use slackbot\handlers\command\CommandHandlerInterface;
use slackbot\handlers\request\RequestHandlerInterface;
use slackbot\models\SlackFacade;
use slackbot\Util;

class CoreProcessor
{
    /** @var array */
    private $requestHandlers = [];

    /** @var array */
    private $actionHandlers = [];

    /** @var array */
    private $timedMessageHandlers = [];

    /** @var array */
    private $commandHandlers = [];

    /** @var SlackFacade */
    private $slackFacade;

    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    public function addRequestHandler(RequestHandlerInterface $handler)
    {
        $this->requestHandlers[] = $handler;
    }

    public function addActionHandler(ActionHandlerInterface $handler)
    {
        $this->actionHandlers[] = $handler;
    }

    public function addTimedMessageHandler($handlerId, $checker, $handler,  $startTime, $finishTime)
    {
        $this->timedMessageHandlers[$handlerId] = [
            'checker' => $checker,
            'handler' => $handler,
            'from' => $startTime,
            'to' => $finishTime,
            'handled' => false
        ];
    }

    public function addCommandHandler(CommandHandlerInterface $handler)
    {
        $this->commandHandlers[] = $handler;
    }


    public function processRequest(RequestDto $dto)
    {
        // First of all, we process RequestInterface classes
        if (count($this->requestHandlers) > 0)
        {
            foreach ($this->requestHandlers as $handler) {
                if ($handler->canProcessRequest($dto)) {
                    if (
                        !$handler->shouldReceiveOwnMessages() &&
                        Util::arrayGet($dto->getData(), 'username') == 'bot'
                    ) {
                        continue;
                    }
                    $handler->processRequest($dto);
                }
            }
        }

        // Afterwards - times messages and !-commands
        if (Util::arrayGet($dto->getData(), 'type') == 'message') {
            $this->processCommand($dto);
            $this->processMessage($dto);
        }
    }

    public function processAction(ActionDto $dto)
    {
        if (count($this->actionHandlers) > 0)
        {
            foreach ($this->actionHandlers as $handler) {
                if ($handler->canProcessAction($dto)) {
                    $handler->processAction($dto);
                }
            }
        }
    }

    public function processMessage(RequestDto $dto) {
        if (!count($this->timedMessageHandlers)) {
            return false;
        }
        foreach ($this->timedMessageHandlers as $handlerId => $handler) {
            if ($handler['handled'] === true) {
                continue;
            }
            $time = time();
            if ($handler['to'] < $time) {
                $this->timedMessageHandlers[$handlerId]['handled'] = false;
                $this->timedMessageHandlers[$handlerId]['timeout'] = true;
                continue;
            }
            $result = call_user_func($handler['checker'], $dto);
            if ($result) {
                $this->timedMessageHandlers[$handlerId]['handled'] = true;
                $this->timedMessageHandlers[$handlerId]['dto'] = $dto;
                call_user_func($handler['handler'], $dto);
            }
        }
    }

    public function isMessageHandled($handlerId) {
        return $this->timedMessageHandlers[$handlerId]['handled'];
    }

    public function isMessageTimedOut($handlerId) {
        return Util::arrayGet($this->timedMessageHandlers[$handlerId], 'timeout') === true;
    }

    public function clearTimedMessageHandlerFlag($handlerId)
    {
        $this->timedMessageHandlers[$handlerId]['handled'] = false;
        unset($this->timedMessageHandlers[$handlerId]['dto']);
    }

    public function getTimedMessageHandleResult($handlerId)
    {
        if ($this->timedMessageHandlers[$handlerId]['handled']) {
            return $this->timedMessageHandlers[$handlerId]['dto'];
        }
        return false;
    }

    public function removeTimedMessageHandler($handlerId)
    {
        unset($this->timedMessageHandlers[$handlerId]);
    }

    public function processCommand(RequestDto $dto)
    {
        if (!count($this->commandHandlers)) {
            return;
        }

        $words = preg_split('/\s+/is', Util::arrayGet($dto->getData(), 'text'));
        $command = Util::arrayGet($words, 0);
        if ($command === null) {
            return;
        }
        if (substr($command, 0, 1) !== '!') {
            return;
        }

        $command = substr($command, 1);

        foreach ($this->commandHandlers as $commandHandler) {
            if ($commandHandler->getName() === $command) {
                $allowed = false;
                $acl = $commandHandler->getAcl();
                if ($acl === CommandHandlerInterface::ACL_ANY) {
                    $allowed = true;
                } else {
                    $currentUser = Util::arrayGet($dto->getData(), 'user');
                    $aclUsers = [];
                    foreach ($acl as $aclItem) {
                        $aclUsers = array_merge($aclUsers, $this->slackFacade->getRecipientUsersIds($aclItem));
                    }
                    $aclUsers = array_unique($aclUsers);

                    if (in_array($currentUser, $aclUsers)) {
                        $allowed = true;
                    }
                }


                if (!$allowed) {
                    $this->slackFacade->multiSendMessage(
                        [ Util::arrayGet($dto->getData(), 'channel') ],
                        sprintf(
                            'SYSTEM: access to command !%s denied',
                            $commandHandler->getName()
                        )
                    );
                    continue;
                }

                unset($words[0]);
                $commandHandler->processCommand($words, Util::arrayGet($dto->getData(), 'channel'));
            }
        }
    }
}
