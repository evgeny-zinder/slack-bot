<?php

namespace slackbot\handlers\action;

use slackbot\CoreProcessor;
use slackbot\dto\ActionDto;
use slackbot\dto\RequestDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\Util;

class UserInputActionHandler extends BaseActionHandler
{
    /** @var string */
    private $handlerId;

    /** @var array */
    private $dtoData;

    /** @var string */
    private $recipientId;

    public function __construct(
        SlackFacade $slackFacade,
        CoreProcessor $coreProcessor
    ) {
        parent::__construct($slackFacade);
        $this->coreProcessor = $coreProcessor;
    }

    /**
     * @param ActionDto $dto
     * @return boolean
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
        if ($beforeMessage !== null) {
            $this->slackFacade->getSlackApi()->chatPostMessage($this->recipientId, $beforeMessage);
        }

        // 2. Register timed message handler
        $this->handlerId = uniqid();
        $start = time();
        $finish = $start + $this->getTimeoutSize(Util::arrayGet($dto->getData(), 'timeout'));
        $this->coreProcessor->addTimedMessageHandler(
            $this->handlerId,
            array($this, 'checker'),
            array($this, 'handler'),
            $start,
            $finish
        );
    }

    public function checker(RequestDto $dto) {
        return (int) Util::arrayGet($dto->getData(), 'text') > 0;
    }

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
            $this->coreProcessor->removeTimedMessageHandler($this->handlerId);

            $response = Util::arrayGet($dto->getData(), 'text');
            Variables::set(
                Util::arrayGet($this->dtoData, 'variable'),
                $response
            );

            // 5. Send "after" message
            $afterMessage = Util::arrayGet(Util::arrayGet($this->dtoData, 'messages'), 'after');
            if ($afterMessage !== null) {
                $this->slackFacade->getSlackApi()->chatPostMessage($this->recipientId, $afterMessage);
            }

            // 6. Processing other actions.
            $afterActions = Util::arrayGet($this->dtoData, 'after');
            if (!count($afterActions)) {
                return;
            }
            foreach ($afterActions as $afterAction) {
                $actionDto = $this->createActionDto();
                $this->populateActionDto($actionDto, $afterAction);
                $this->coreProcessor->processAction($actionDto);
            }
        }

    }
    private function getTimeoutSize($timeout) {
        switch (substr($timeout, -1)) {
            case 's': $multiplyer = 1; break;
            case 'm': $multiplyer = 60; break;
            case 'h': $multiplyer = 60 * 60; break;
            default: $multiplyer = 1; break;
        }
        return (int) (substr($timeout, 0, -1) * $multiplyer);
    }
}
