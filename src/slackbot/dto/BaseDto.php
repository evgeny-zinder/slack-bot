<?php

namespace slackbot\dto;


use eznio\ar\Ar;

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
     * @return $this
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
        return Ar::get($this->data, $field);
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
