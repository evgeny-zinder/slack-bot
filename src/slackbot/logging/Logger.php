<?php

namespace slackbot\logging;


use eznio\ar\Ar;
use slackbot\logging\handlers\HandlerInterface;

class Logger
{
    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 4;
    const TYPE_CRITICAL = 8;

    const TYPE_NAMES_REV = [
        'info' => self::TYPE_INFO,
        'warning' => self::TYPE_WARNING,
        'error' => self::TYPE_ERROR,
        'critical' => self::TYPE_CRITICAL,
    ];

    const TYPE_NAMES = [
        self::TYPE_INFO => 'info',
        self::TYPE_WARNING => 'warning',
        self::TYPE_ERROR => 'error',
        self::TYPE_CRITICAL => 'critical'
    ];

    protected $handlers = [];

    /** @var NamesResolver */
    protected $namesResolver;

    public function __construct(NamesResolver $namesResolver)
    {
        $this->namesResolver = $namesResolver;
    }

    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    public function getHandlers()
    {
        return $this->handlers;
    }

    public function info($message)
    {
        $this->send(self::TYPE_INFO, $message);
    }

    public function warning($message)
    {
        $this->send(self::TYPE_WARNING, $message);
    }

    public function error($message)
    {
        $this->send(self::TYPE_ERROR, $message);
    }

    public function critical($message)
    {
        $this->send(self::TYPE_CRITICAL, $message);
    }

    public function send($type, $message) {
        $message = $this->resolveNamesInMessage($message);
        Ar::each($this->handlers, function($handler) use ($type, $message) {
            /** @var HandlerInterface $handler */
            $handler->send($type, $message);
        });

    }

    protected function resolveNamesInMessage($message)
    {
        return $this->namesResolver->resolve($message);
    }
}
