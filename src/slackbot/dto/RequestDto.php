<?php

namespace slackbot\dto;

class RequestDto extends BaseDto
{
    /** @var string */
    protected $id = null;

    /** @var string */
    private $source;

    public function getId()
    {
        if (null === $this->id) {
            $this->id = uniqid();
        }
        return $this->id;
    }

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

    /**
     * @return bool
     */
    public function isBotMessage()
    {
        return (null !== $this->get('bot_id')) || ('bot_message' === $this->get('subtype'));
    }
}
