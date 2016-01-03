<?php

namespace slackbot\dto;

class RequestDto extends BaseDto
{
    /** @var string */
    private $source;

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
     * @return string
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->get('text');
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->get('channel');
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->get('type');
    }
}
