<?php

namespace slackbot\dto;

use slackbot\Util;

class BaseDto
{
    /** @var array */
    protected $data;

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
     * @param string $field field to get
     * @return mixed
     */
    public function get($field)
    {
        return Util::arrayGet($this->data, $field);
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->get('message');
    }
}
