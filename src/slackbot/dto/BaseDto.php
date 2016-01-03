<?php

namespace slackbot\dto;

use slackbot\Util;

class BaseDto
{
    /** @var array */
    private $data;

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return RequestDto
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return Util::arrayGet($this->data, 'user');
    }

}
