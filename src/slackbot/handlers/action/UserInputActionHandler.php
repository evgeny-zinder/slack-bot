<?php

namespace slackbot\handlers\action;

use slackbot\CoreProcessor;
use slackbot\dto\ActionDto;
use slackbot\dto\RequestDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\Util;

/**
 * Class UserInputActionHandler
 * @package slackbot\handlers\action
 */
class UserInputActionHandler extends BaseActionHandler
{
    /** @var string */
    private $handlerId;

    /** @var array */
    private $dtoData;

    /** @var string */
    private $recipientId;

    /**
     * UserInputActionHandler constructor.
     * @param SlackFacade $slackFacade
     * @param CoreProcessor $coreProcessor
     */
    public function __construct(
        SlackFacade $slackFacade,
        CoreProcessor $coreProcessor
    ) {
        parent::__construct($slackFacade);
        $this->coreProcessor = $coreProcessor;
    }

    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return Util::arrayGet($dto->getData(), 'action') === 'user_input';
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        $this->dtoData = $dto->getData();
        $recipient = Util::arrayGet($dto->getData(), 'recipient');
        $this->recipientId = $this->slackFacade->getUserIdByName(str_replace('@', '', $recipient));
        $messages = Util::arrayGet($dto->getData(), 'messages');

        // 1. Send "before" message
        $beforeMessage = Util::arrayGet($messages, 'before');
        if (null !== $beforeMessage) {
            $this->slackFacade->getSlackApi()->chatPostMessage($this->recipientId, $beforeMessage);
        }

        // 2. Register timed message handler
        $this->handlerId = uniqid();
        $start = time();
        $finish = $start + $this->getTimeoutSize(Util::arrayGet($dto->getData(), 'timeout'));
        $this->coreProcessor->addTimedMessageHandler(
            $this->handlerId,
            [$this, 'checker'],
            [$this, 'handler'],
            $start,
            $finish
        );
    }

    /**
     * @param RequestDto $dto
     * @return bool
     */
    public function checker(RequestDto $dto) {
        return (int) Util::arrayGet($dto->getData(), 'text') > 0;
    }

    /**
     * @param RequestDto $dto
     * @return null
     */
    public function handler(RequestDto $dto) {
        // 4a. Timeout, exiting
        if ($this->coreProcessor->isMessageTimedOut($this->handlerId)) {
            $this->slackFacade->getSlackApi()->chatPostMessage($this->recipientId, 'Sorry, response timed out');
            $this->coreProcessor->removeTimedMessageHandler($this->handlerId);
            return;
        }

        // 4b. Processed. Setting variable.
        if ($this->coreProcessor->isMessageHandled($this->handlerId)) {
            $dto = $this->coreProcessor->getTimedMessageHandleResult($this->handlerId);
            if (null === $dto) {
                return;
            }
            $this->coreProcessor->removeTimedMessageHandler($this->handlerId);

            $response = Util::arrayGet($dto->getData(), 'text');
            Variables::set(
                Util::arrayGet($this->dtoData, 'variable'),
                $response
            );

            // 5. Send "after" message
            $afterMessage = Util::arrayGet(Util::arrayGet($this->dtoData, 'messages'), 'after');
            if (null !== $afterMessage) {
                $this->slackFacade->getSlackApi()->chatPostMessage($this->recipientId, $afterMessage);
            }

            // 6. Processing other actions.
            $afterActions = Util::arrayGet($this->dtoData, 'after');
            if (0 === count($afterActions)) {
                return;
            }
            foreach ($afterActions as $afterAction) {
                $actionDto = $this->createActionDto();
                $this->populateActionDto($actionDto, $afterAction);
                $this->coreProcessor->processAction($actionDto);
            }
        }

    }

    /**
     * @param string $timeout timeout string in h/m/s, optionally - with unit at the end
     * @return int timeout size in seconds
     */
    private function getTimeoutSize($timeout) {
        switch (substr($timeout, -1)) {
            case 's': $multiplier = 1; break;
            case 'm': $multiplier = 60; break;
            case 'h': $multiplier = 60 * 60; break;
            default: $multiplier = 1; break;
        }
        return (int) (substr($timeout, 0, -1) * $multiplier);
    }
}
