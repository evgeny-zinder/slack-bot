<?php

namespace slackbot\logging\handlers;


interface HandlerInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @param $type
     * @param $message
     * @return null
     */
    public function send($type, $message);
}
