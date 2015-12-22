<?php

namespace slackbot;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\Util;

/**
 * Class OutputManager
 * @package slackbot
 */
class OutputManager
{
    /** @var SlackFacade */
    private $slackFacade;

    /**
     * OutputManager constructor.
     * @param SlackFacade $slackFacade
     */
    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    /**
     * @param ActionDto $dto
     */
    public function sendMessage(ActionDto $dto)
    {
        $recipients = $this->parseRecipients(Util::arrayGet($dto->getData(), 'recipients'));
        $this->slackFacade->multiSendMessage(
            $recipients,
            Util::arrayGet($dto->getData(), 'message'),
            [
                'as_user' => true
            ]
        );

    }

    /**
     * @param $recipientsString
     * @return array
     */
    public function parseRecipients($recipientsString)
    {
        $recipients = preg_split('/\s*,\s*/', $recipientsString);
        $recipientIds = [];
        if (0 === count($recipients)) {
            return $recipientIds;
        }
        foreach ($recipients as $recipient) {
            if ($recipient !== null) {
                $recipientIds[] = $this->slackFacade->getRecipientIdByName($recipient);
            }
        }
        return $recipientIds;
    }
}
