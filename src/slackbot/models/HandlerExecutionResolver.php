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

        $dtoChannel = $dto->getChannel();
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

        if ('C' === $dtoChannelType) {
            $dtoChannelInfo = $this->slackApi->channelsInfo($dtoChannel);
            $dtoChannelName = '#' . Util::arrayGet($dtoChannelInfo, 'channel.name');
        } else {
            $dtoChannelInfo = $this->slackApi->groupsInfo($dtoChannel);
            $dtoChannelName = Util::arrayGet($dtoChannelInfo, 'group.name');
        }

        $defaultBehavior = $this->config->getEntry('custom.' . $handlerId . '.default');
        $handlerChannels = $this->config->getEntry('custom.' . $handlerId . '.channels');
        if (empty($handlerChannels)) {
            return 'allow' === $defaultBehavior ? true : false;
        }
        $handlerChannelConfig = $this->config->getSectionFromArray(
            'custom.' . $handlerId . '.channels',
            'name=' . $dtoChannelName
        );
        if (empty($handlerChannelConfig)) {
            return 'allow' === $defaultBehavior ? true : false;
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
