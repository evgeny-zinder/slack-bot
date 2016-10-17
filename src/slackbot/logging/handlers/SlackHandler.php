<?php

namespace slackbot\logging\handlers;


use slackbot\logging\Logger;
use slackbot\models\SlackFacade;

class SlackHandler implements HandlerInterface
{
    /** @var SlackFacade  */
    protected $slackFacade;

    /** @var array */
    protected $channels = [];

    /** @var int */
    protected $filter = 255;

    /**
     * SlackHandler constructor.
     * @param SlackFacade $slackFacade
     */
    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'slackHandler';
    }

    /**
     * @param array $channels
     * @return $this
     */
    public function setChannels(array $channels)
    {
        $this->channels = $channels;
        return $this;
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
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
     * @return null
     */
    public function send($type, $message)
    {
        if (false === $this->isFiltered($type)) {
            $options = [ 'in_logger' => true ];
            $this->slackFacade->multiSendMessage($this->channels, '```' . substr($message, 0, 3000) . '```', $options);
        }
    }

    /**
     * @param $type
     * @return bool|int
     */
    protected function isFiltered($type)
    {
        if (null === $type) {
            return false;
        }
        
        return ($type & $this->filter) != $type;
    }
}
