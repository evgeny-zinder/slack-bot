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
use eznio\ar\Ar;
use slackbot\logging\Logger;
use slackbot\models\Config;

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
        Logger::get()->debug("id: %s, staring overall request processing", $dto->getId());

        Logger::get()->debug("id: %s, staring raw request processing", $dto->getId());
        $this->processRequest($dto);
        Logger::get()->debug("id: %s, finished raw request processing", $dto->getId());

        if ('message' === $dto->getType()) {

            Logger::get()->message(
                "id: %s, channel: %s, user: %s, message: %s",
                $dto->getId(),
                $dto->getChannel(),
                $dto->getUser(),
                $dto->getText()
            );

            Logger::get()->debug("id: %s, started command processing", $dto->getId());
            $this->processCommand($dto);
            Logger::get()->debug("id: %s, finished command processing", $dto->getId());

            Logger::get()->debug("id: %s, started message processing", $dto->getId());
            $this->processMessage($dto);
            Logger::get()->debug("id: %s, finished message processing", $dto->getId());
        }
        Logger::get()->debug("id: %s, finished overall request processing", $dto->getId());
    }

    /**
     * Processing raw request
     * @param RequestDto $dto
     */
    protected function processRequest(RequestDto $dto)
    {
        Logger::get()->raw("id: %s, staring raw request processing", $dto->getId());

        if (0 === count($this->requestHandlers)) {
            Logger::get()->raw("is: %s, no request handlers found, stopper raw request processing", $dto->getId());
            return;
        }

        if (true === $dto->isBotMessage()) {
            Logger::get()->raw("is: %s, bot message, stopped raw request processing", $dto->getId());
            return;
        }

        /** @var RequestHandlerInterface $handler */
        foreach ($this->requestHandlers as $handler) {
            if ($handler->canProcessRequest($dto)) {
                if (
                    !$handler->shouldReceiveOwnMessages() &&
                    'bot' === $dto->getUser()
                ) {
                    continue;
                }

                if (!$this->executionResolver->shouldExecute($handler, $dto)) {
                    continue;
                }
                $result = $handler->processRequest($dto, $this->executionResolver->getParams());
                if ($result === RequestHandlerInterface::STOP_PROCESSING) {
                    return;
                }
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
        return Ar::get($this->timedMessageHandlers[$handlerId], 'timeout') === true;
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
        $command = Ar::get($words, 0);
        if (null === $command || '!' !== substr($command, 0, 1)) {
            return;
        }

        $command = substr($command, 1);

        Logger::get()->info(
            "id: %s, %s is executing command \"%s\" at %s",
            $dto->getId(),
            $dto->getUser(),
            $dto->getText(),
            $dto->getChannel()
        );

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
                Logger::get()->warning(
                    "id: %s, access denied for %s trying to run \"%s\" at %s",
                    $dto->getId(),
                    $dto->getUser(),
                    $dto->getText(),
                    $dto->getChannel()
                );
                return;
            }

            unset($words[0]);
            if ($commandHandler->canProcessCommand($words, $dto->getChannel())) {
                /** @var SlackFacade $slackFacade */
                $slackFacade = Registry::get('container')['slack_facade'];
                $commandHandler->setCallerId($dto->getUser());
                $commandHandler->setCallerName($slackFacade->getUserNameById($dto->getUser()));

                Logger::get()->debug(
                    "id: %s, starting %s command handler",
                    $dto->getId(),
                    $commandHandler->getName()
                );
                $commandHandler->processCommand($words, $dto->getChannel());
                Logger::get()->debug(
                    "id: %s, finished %s command handler",
                    $dto->getId(),
                    $commandHandler->getName()
                );

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
        /** @var Config $config */
        $config = Registry::get('container')['config'];
        $acl = $commandHandler->getAcl();
        if (CommandHandlerInterface::ACL_ANY === $acl) {
            return true;
        } elseif (CommandHandlerInterface::ACL_ADMIN === $acl) {
            $currentUser = $dto->getUser();
            $currentUserName = $this->slackFacade->getUserNameById($currentUser);

            $admins = $config->getEntry('acl.admins') ?: [];
            if (0 === count($admins)) {
                return false;
            }
            return in_array($currentUserName, $admins);
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

            return in_array($currentUser, $aclUsers);
        }
    }
}
