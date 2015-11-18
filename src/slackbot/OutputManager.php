<?php

namespace slackbot;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\Util;

class OutputManager
{
    private $slackFacade;

    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

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

    public function parseRecipients($recipientsString)
    {
        $recipients = preg_split('/\s*,\s*/', $recipientsString);
        if (!count($recipients)) {
            return [];
        }
        $recipientIds = [];
        foreach ($recipients as $recipient) {
            if ($recipient !== null) {
                $recipientIds[] = $this->slackFacade->getRecipientIdByName($recipient);
            }
        }
        return $recipientIds;
    }
}
