<?php

namespace slackbot\dto;

class ActionDto
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
}
