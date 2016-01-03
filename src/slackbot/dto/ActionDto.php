<?php

namespace slackbot\dto;

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
