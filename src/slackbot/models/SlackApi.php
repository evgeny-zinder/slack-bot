<?php

namespace slackbot\models;

use slackbot\util\CurlRequest;

/**
 * Class SlackApi
 * @package slackbot\models
 */
class SlackApi
{
    const BASE_URL = 'https://slack.com/api/';

    /** @var string */
    private $token;

    /** @var CurlRequest */
    private $curlRequest;

    /**
     * SlackApi constructor.
     * @param CurlRequest $curlRequest
     */
    public function __construct(CurlRequest $curlRequest) {
        $this->curlRequest = $curlRequest;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Posts message to channel/private group/direct message
     * @param string $channel recipient channel ID
     * @param string $message message to send
     * @param array $options extra slack formatting options
     * @return array slack response
     */
    public function chatPostMessage($channel, $message, $options = []) {
        $options['channel'] = $channel;
        $options['text'] = $message;
        return $this->processRequest(__FUNCTION__, $options);
    }

    /**
     * Return list of current team's users
     * @return array
     */
    public function usersList()
    {
        return $this->processRequest(__FUNCTION__);
    }

    /**
     * Return list of current team's channels
     * @return array
     */
    public function channelsList()
    {
        return $this->processRequest(__FUNCTION__);
    }

    /**
     * Returns channel's info
     * @param string $channelId
     * @return array
     */
    public function channelsInfo($channelId)
    {
        $options['channel'] = $channelId;
        return $this->processRequest(__FUNCTION__, $options);
    }

    /**
     * Return list of private groups accessible by bot
     * @return array
     */
    public function groupsList()
    {
        return $this->processRequest(__FUNCTION__);
    }

    /**
     * Returns private group's info
     * @param string $groupId
     * @return array
     */
    public function groupsInfo($groupId)
    {
        $options['channel'] = $groupId;
        return $this->processRequest(__FUNCTION__, $options);
    }

    /**
     * Opens IM channel with requested user
     * @param string $userId
     * @return array
     */
    public function imOpen($userId)
    {
        $options['user'] = $userId;
        return $this->processRequest(__FUNCTION__, $options);
    }

    /**
     * Gets Slack API method name from called method name
     * @param string $method
     * @return string
     */
    private function getApiMethodName($method)
    {
        preg_match('/^[a-z]+/', $method, $m);
        $groupName = $m[0];
        $method = str_replace($groupName, '', $method);
        $method[0] = strtolower($method[0]);
        return $groupName . '.' . $method;
    }

    /**
     * Fires request to Slack server and returns response
     * @param string $method Slack API method to call
     * @param array $data request data
     * @return array
     * @throws \Exception
     */
    private function processRequest($method, $data = []) {
        $method = $this->getApiMethodName($method);
        $url = self::BASE_URL . $method;
        $data['token'] = $this->token;

        $result = $this->curlRequest->getCurlResult(
            $url,
            [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data
            ]
        )['body'];
        return json_decode($result, true);
    }
}
