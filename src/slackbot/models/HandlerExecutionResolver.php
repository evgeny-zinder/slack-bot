<?php

namespace slackbot\models;

use slackbot\dto\RequestDto;
use slackbot\handlers\request\RequestHandlerInterface;
use eznio\ar\Ar;

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
        $section = $this->config->getSection('custom');
        if (empty($section)) {
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
            return Ar::get($handlerConfig, 'dm') ?: true;
        }

        if ('C' === $dtoChannelType) {
            $dtoChannelInfo = $this->slackApi->channelsInfo($dtoChannel);
            $dtoChannelName = '#' . Ar::get($dtoChannelInfo, 'channel.name');
        } else {
            $dtoChannelInfo = $this->slackApi->groupsInfo($dtoChannel);
            $dtoChannelName = Ar::get($dtoChannelInfo, 'group.name');
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

        $this->params = Ar::get($handlerChannelConfig, 'params') ?: [];
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
