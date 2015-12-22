<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\Variables;
use slackbot\OutputManager;
use slackbot\models\SlackFacade;
use slackbot\Util;

/**
 * Class SendMessageActionHandler
 * @package slackbot\handlers\action
 */
class SendMessageActionHandler extends BaseActionHandler
{
    /** @var OutputManager */
    private $outputManager;

    /**
     * SendMessageActionHandler constructor.
     * @param SlackFacade $slackFacade
     * @param OutputManager $outputManager
     */
    public function __construct(SlackFacade $slackFacade, OutputManager $outputManager)
    {
        parent::__construct($slackFacade);
        $this->outputManager = $outputManager;
    }

    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return Util::arrayGet($dto->getData(), 'action') === 'send_message';
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        $recipients = preg_split('/\s*,\s*/', Util::arrayGet($dto->getData(), 'recipients'));
        if (0 === count($recipients)) {
            return;
        }
        $recipientIds = [];
        foreach ($recipients as $recipient) {
            if ($recipient !== null) {
                $recipientIds[] = $this->slackFacade->getRecipientIdByName($recipient);
            }
        }
        if (0 === count($recipientIds)) {
            return;
        }
        $message = Util::arrayGet($dto->getData(), 'message');
        $message = $this->substituteVariables($message);
        $dto->setData(array_merge($dto->getData(), ['message' => $message]));
        $this->outputManager->sendMessage($dto);
    }

    /**
     * @param $string
     * @return string
     */
    private function substituteVariables($string)
    {
        $vars = Variables::all();
        if (0 === count($vars)) {
            return $string;
        }
        foreach ($vars as $name => $value) {
            $string = str_replace($name, $value, $string);
        }
        return $string;
    }

}
