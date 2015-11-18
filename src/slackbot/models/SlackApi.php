<?php

namespace slackbot\models;

use slackbot\util\CurlRequest;

class SlackApi
{
    const BASE_URL = 'https://slack.com/api/';

    private $token;
    private $curlRequest;

    public function __construct($token, CurlRequest $curlRequest) {
        $this->token = $token;
        $this->curlRequest = $curlRequest;
    }

    public function chatPostMessage($channel, $message, $options = []) {
        $options['channel'] = $channel;
        $options['text'] = $message;
        return $this->processRequest(__FUNCTION__, $options);
    }

    public function usersList()
    {
        return $this->processRequest(__FUNCTION__);
    }

    public function channelsList()
    {
        return $this->processRequest(__FUNCTION__);
    }
    public function channelsInfo($channelId)
    {
        $options['channel'] = $channelId;
        return $this->processRequest(__FUNCTION__, $options);
    }

    public function groupsList()
    {
        return $this->processRequest(__FUNCTION__);
    }
    public function groupsInfo($groupId)
    {
        $options['channel'] = $groupId;
        return $this->processRequest(__FUNCTION__, $options);
    }


    public function imOpen($userId)
    {
        $options['user'] = $userId;
        return $this->processRequest(__FUNCTION__, $options);
    }


    private function getApiMethodName($method)
    {
        preg_match('/^[a-z]+/', $method, $m);
        $groupName = $m[0];
        $method = str_replace($groupName, '', $method);
        $method[0] = strtolower($method[0]);
        return $groupName . '.' . $method;
    }

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
