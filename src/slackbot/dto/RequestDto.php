<?php

namespace slackbot\dto;

use slackbot\Util;

class RequestDto
{
    /** @var string */
    private $source;

    /** @var array */
    private $data;

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return RequestDto
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

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
    public function getMessage()
    {
        return Util::arrayGet($this->data, 'message');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return Util::arrayGet($this->data, 'username');
    }

    /**
     * @return string
     */
    public function getText()
    {
        return Util::arrayGet($this->data, 'text');
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return Util::arrayGet($this->data, 'channel');
    }
}
