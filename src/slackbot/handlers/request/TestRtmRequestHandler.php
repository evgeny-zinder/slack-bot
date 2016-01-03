<?php

namespace slackbot\handlers\request;

use slackbot\Util;
use slackbot\dto\RequestDto;

class TestRtmRequestHandler extends BaseRequestHandler
{
    /**
     * @param RequestDto $dto
     * @return bool
     */
    public function canProcessRequest(RequestDto $dto)
    {
        if ($dto->getSource() === 'rtm') {
            $data = $dto->getData();
            if (
                Util::arrayGet($data, 'type') === 'message'
            ) {
                return true;
            }
        }
        return false;
    }

    public function shouldReceiveOwnMessages()
    {
        return false;
    }

    /**
     * @param RequestDto $dto
     * @return null
     */
    public function processRequest(RequestDto $dto, array $params)
    {
        $messageText = Util::arrayGet($dto->getData(), 'text');

        $this->slackFacade->getSlackApi()->chatPostMessage(
            $dto->getData()['channel'],
            'Said: ' . $messageText . ', params: ' . json_encode($params),
            [
                'parse' => 'full'
            ]
        );
    }
}
