<?php

namespace slackbot\dto;

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
}
