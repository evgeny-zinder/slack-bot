<?php

namespace slackbot\logging;


use eznio\ar\Ar;
use slackbot\logging\handlers\HandlerInterface;
use slackbot\models\Registry;

class Logger
{
    const TYPE_CRITICAL = 1;
    const TYPE_ERROR = 2;
    const TYPE_WARNING = 4;
    const TYPE_INFO = 8;
    const TYPE_DEBUG = 16;
    const TYPE_MESSAGE = 32;
    const TYPE_RAW = 64;

    const TYPE_NAMES = [
        self::TYPE_CRITICAL => 'critical',
        self::TYPE_ERROR => 'error',
        self::TYPE_WARNING => 'warning',
        self::TYPE_INFO => 'info',
        self::TYPE_DEBUG => 'debug',
        self::TYPE_MESSAGE => 'message',
        self::TYPE_RAW => 'raw',
    ];

    protected $handlers = [];

    /** @var NamesResolver */
    protected $namesResolver;

    /** @var bool */
    protected $shouldResolveNames = false;

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

    public function setResolveNames($resolve)
    {
        $this->shouldResolveNames = $resolve;
    }

    public function raw($message, ...$placeholders)
    {
        $this->send(self::TYPE_RAW, $message, $placeholders);
    }

    public function message($message, ...$placeholders)
    {
        $this->send(self::TYPE_MESSAGE, $message, $placeholders);
    }

    public function debug($message, ...$placeholders)
    {
        $this->send(self::TYPE_DEBUG, $message, $placeholders);
    }

    public function info($message, ...$placeholders)
    {
        $this->send(self::TYPE_INFO, $message, $placeholders);
    }

    public function warning($message, ...$placeholders)
    {
        $this->send(self::TYPE_WARNING, $message, $placeholders);
    }

    public function error($message, ...$placeholders)
    {
        $this->send(self::TYPE_ERROR, $message, $placeholders);
    }

    public function critical($message, ...$placeholders)
    {
        $this->send(self::TYPE_CRITICAL, $message, $placeholders);
    }

    public function send($type, $message, $placeholders = []) {
        if (count($placeholders) > 0) {
            $placeholders = array_merge([$message], $placeholders);
            $message = call_user_func_array('sprintf', $placeholders);
        }
        if (true === $this->shouldResolveNames && self::TYPE_RAW !== $type) {
            $message = $this->resolveNamesInMessage($message);
        }

        Ar::each($this->handlers, function($handler) use ($type, $message) {
            /** @var HandlerInterface $handler */
            $handler->send($type, $message);
        });
    }

    /**
     * @return Logger
     */
    public static function get()
    {
        $container = Registry::get('container');
        return $container['logger'];
    }

    protected function resolveNamesInMessage($message)
    {
        return $this->namesResolver->resolve($message);
    }
}
