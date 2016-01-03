<?php

namespace slackbot\models;

use slackbot\dto\RequestDto;
use slackbot\handlers\request\RequestHandlerInterface;
use slackbot\Util;

/**
 * Class HandlerExecutionResolver
 * @package slackbot\models
 */
class HandlerExecutionResolver
{
    /** @var Config */
    private $config;

    /** @var SlackApi */
    private $slackApi;

    /** @var array */
    private $params = [];

    /**
     * HandlerExecutionResolver constructor.
     * @param Config $config
     * @param SlackApi $slackApi
     */
    public function __construct(Config $config, SlackApi $slackApi)
    {
        $this->config = $config;
        $this->slackApi = $slackApi;
    }

    /**
     * @param RequestHandlerInterface $handler
     * @param RequestDto $dto
     * @return bool
     */
    public function shouldExecute(RequestHandlerInterface $handler, RequestDto $dto)
    {
        if (empty($this->config->getSection('custom'))) {
            return true;
        }

        $dtoChannel = Util::arrayGet($dto->getData(), 'channel');
        if (null === $dtoChannel) {
            return true;
        }

        $handlerId = $handler->getId();
        $handlerConfig = $this->config->getEntry('custom.' . $handlerId);
        if (empty($handlerConfig)) {
            return false;
        }

        $dtoChannelType = $dtoChannel[0];
        if ('D' === $dtoChannelType) {
            return Util::arrayGet($handlerConfig, 'dm') ?: true;
        }

        $dtoChannelInfo = $this->slackApi->channelsInfo($dtoChannel);
        $dtoChannelName = Util::arrayGet($dtoChannelInfo, 'channel.name');
        if ('C' === $dtoChannelType) {
            $dtoChannelName = '#' . $dtoChannelName;
        }

        $handlerChannels = $this->config->getEntry('custom.' . $handlerId . '.channels');
        if (empty($handlerChannels)) {
            return false;
        }
        $handlerChannelConfig = $this->config->getSectionFromArray(
            'custom.' . $handlerId . '.channels',
            'name=' . $dtoChannelName
        );
        if (empty($handlerChannelConfig)) {
            return false;
        }

        $this->params = Util::arrayGet($handlerChannelConfig, 'params') ?: [];
        return true;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}