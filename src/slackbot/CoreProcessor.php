<?php

namespace slackbot;

use slackbot\dto\ActionDto;
use slackbot\dto\RequestDto;
use slackbot\handlers\action\ActionHandlerInterface;
use slackbot\handlers\command\BaseCommandHandler;
use slackbot\handlers\command\CommandHandlerInterface;
use slackbot\handlers\request\RequestHandlerInterface;
use slackbot\models\HandlerExecutionResolver;
use slackbot\models\Registry;
use slackbot\models\SlackFacade;
use slackbot\Util;

/**
 * Class CoreProcessor
 * Provides core functions to process requests, messages and playbook actions
 * @package slackbot
 */
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

    /** @var HandlerExecutionResolver */
    private $executionResolver;

    /**
     * CoreProcessor constructor.
     * @param SlackFacade $slackFacade
     * @param HandlerExecutionResolver $executionResolver
     */
    public function __construct(SlackFacade $slackFacade, HandlerExecutionResolver $executionResolver)
    {
        $this->slackFacade = $slackFacade;
        $this->executionResolver = $executionResolver;
    }

    /**
     * Adds request handler to queue
     * @param RequestHandlerInterface $handler
     */
    public function addRequestHandler(RequestHandlerInterface $handler)
    {
        $this->requestHandlers[] = $handler;
    }

    /**
     * Adds action handler to queue
     * @param ActionHandlerInterface $handler
     */
    public function addActionHandler(ActionHandlerInterface $handler)
    {
        $this->actionHandlers[] = $handler;
    }

    /**
     * Adds timed message handler to queue
     * If message is not processed during requested time - processing will fail
     * @param string $handlerId ID to identify handler
     * @param callable $checker function to check if message should be processes
     * @param callable $handler function to process message
     * @param int $startTime unix timestamp to start processing
     * @param int $finishTime unix timestamp to stop processing
     */
    public function addTimedMessageHandler($handlerId, $checker, $handler, $startTime, $finishTime)
    {
        $this->timedMessageHandlers[$handlerId] = [
            'checker' => $checker,
            'handler' => $handler,
            'from' => $startTime,
            'to' => $finishTime,
            'handled' => false
        ];
    }

    /**
     * Adds command handler to queue
     * @param CommandHandlerInterface $handler
     */
    public function addCommandHandler(CommandHandlerInterface $handler)
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * Main processing chain
     * @param RequestDto $dto
     */
    public function process(RequestDto $dto)
    {
        $this->processRequest($dto);

        if ('message' === $dto->getType()) {
            $this->processCommand($dto);
            $this->processMessage($dto);
        }
    }

    /**
     * Processing raw request
     * @param RequestDto $dto
     */
    protected function processRequest(RequestDto $dto)
    {
        if (0 === count($this->requestHandlers)) {
            return;
        }

        /** @var RequestHandlerInterface $handler */
        foreach ($this->requestHandlers as $handler) {
            if ($handler->canProcessRequest($dto)) {
                if (
                    !$handler->shouldReceiveOwnMessages() &&
                    'bot' === $dto->getUsername()
                ) {
                    continue;
                }

                if (!$this->executionResolver->shouldExecute($handler, $dto)) {
                    continue;
                }
                $handler->processRequest($dto, $this->executionResolver->getParams());
            }
        }
    }

    /**
     * Processing playbook action
     * @param ActionDto $dto
     */
    public function processAction(ActionDto $dto)
    {
        if (0 === count($this->actionHandlers)) {
            return;
        }

        /** @var ActionHandlerInterface $handler */
        foreach ($this->actionHandlers as $handler) {
            if ($handler->canProcessAction($dto)) {
                $handler->processAction($dto);
            }
        }
    }

    /**
     * Processes single user/bot message
     * @param RequestDto $dto
     * @return boolean|null
     */
    public function processMessage(RequestDto $dto)
    {
        if (0 === count($this->timedMessageHandlers)) {
            return;
        }
        foreach ($this->timedMessageHandlers as $handlerId => $handler) {
            if (true === $handler['handled']) {
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

    /**
     * Checks if message was processed successfully
     * @param string $handlerId
     * @return bool
     */
    public function isMessageHandled($handlerId)
    {
        return $this->timedMessageHandlers[$handlerId]['handled'];
    }

    /**
     * Checks if message has timed out
     * @param string $handlerId
     * @return bool
     */
    public function isMessageTimedOut($handlerId)
    {
        return Util::arrayGet($this->timedMessageHandlers[$handlerId], 'timeout') === true;
    }

    /**
     * Clears timed message's "handled" flag
     * @param string $handlerId
     */
    public function clearTimedMessageHandlerFlag($handlerId)
    {
        $this->timedMessageHandlers[$handlerId]['handled'] = false;
        unset($this->timedMessageHandlers[$handlerId]['dto']);
    }

    /**
     * Returns timed message processing result or false if still unprocessed
     * @param string $handlerId
     * @return ActionDto|null
     */
    public function getTimedMessageHandleResult($handlerId)
    {
        if ($this->timedMessageHandlers[$handlerId]['handled']) {
            return $this->timedMessageHandlers[$handlerId]['dto'];
        }
        return null;
    }

    /**
     * Removes timed message handler
     * @param string $handlerId
     */
    public function removeTimedMessageHandler($handlerId)
    {
        unset($this->timedMessageHandlers[$handlerId]);
    }

    /**
     * Processes !-command
     * @param RequestDto $dto
     */
    public function processCommand(RequestDto $dto)
    {
        $words = preg_split('/\s+/is', $dto->getText());
        $command = Util::arrayGet($words, 0);
        if (null === $command || '!' !== substr($command, 0, 1)) {
            return;
        }

        $command = substr($command, 1);

        /** @var BaseCommandHandler $commandHandler */
        foreach ($this->commandHandlers as $commandHandler) {
            $commandName = $commandHandler->getName();
            if (is_array($commandName)) {
                if (!in_array($command, $commandName)) {
                    continue;
                }
            } else {
                if ($commandName !== $command) {
                    continue;
                }
            }

            $allowed = $this->checkAccess($dto, $commandHandler);
            if (!$allowed) {
                $this->slackFacade->multiSendMessage(
                    [$dto->getChannel()],
                    sprintf(
                        'SYSTEM: access to command !%s denied',
                        $commandHandler->getName()
                    )
                );
                continue;
            }

            unset($words[0]);
            if ($commandHandler->canProcessCommand($words, $dto->getChannel())) {
                /** @var SlackFacade $slackFacade */
                $slackFacade = Registry::get('container')['slack_facade'];
                $commandHandler->setCallerId($dto->getUser());
                $commandHandler->setCallerName($slackFacade->getUserNameById($dto->getUser()));

                $commandHandler->processCommand($words, $dto->getChannel());
            }
        }
    }

    /**
     * @param RequestDto $dto
     * @param $commandHandler
     * @return bool
     */
    protected function checkAccess(RequestDto $dto, CommandHandlerInterface $commandHandler)
    {
        $acl = $commandHandler->getAcl();
        if (CommandHandlerInterface::ACL_ANY === $acl) {
            return true;
        } else {
            if (!is_array($acl)) {
                throw new \RuntimeException('Wrong ACL format: array expected');
            }
            $currentUser = $dto->getUser();
            $aclUsers = [];
            foreach ($acl as $aclItem) {
                $aclUsers = array_merge($aclUsers, $this->slackFacade->getRecipientUsersIds($aclItem));
            }
            $aclUsers = array_unique($aclUsers);

            if (in_array($currentUser, $aclUsers)) {
                return true;
            }
        }
        return false;
    }
}
