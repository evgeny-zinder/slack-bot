<?php

namespace slackbot\logging\handlers;


use eznio\ar\Ar;
use slackbot\logging\Logger;

class ConsoleOutputHandler implements HandlerInterface
{
    /** @var int */
    protected $filter = 255;

    /**
     * @return string
     */
    public function getId()
    {
        return 'consoleOutputHandler';
    }

    /**
     * @param $filter
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return int
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param $type
     * @param $message
     * @return void
     */
    public function send($type, $message)
    {
        echo sprintf(
            "[%s] %s\n",
            strtoupper(Ar::get(Logger::TYPE_NAMES, $type)) ?: '?',
            $message
        );
    }

    /**
     * @param $type
     * @return bool
     */
    protected function isFiltered($type)
    {
        if (null === $type) {
            return false;
        }

        return !(($type & $this->filter) == $type);
    }
}
