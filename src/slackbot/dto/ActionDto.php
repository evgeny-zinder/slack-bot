<?php

namespace slackbot\dto;

use slackbot\Util;

class ActionDto extends BaseDto
{
    /**
     * @return mixed
     */
    public function getRecipients()
    {
        return $this->get('recipients');
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->get('action');
    }
}
