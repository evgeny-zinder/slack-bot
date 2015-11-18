<?php

namespace slackbot\models;

use slackbot\Util;

class SlackFacade
{
    /** @var SlackApi */
    private $slackApi;

    public function __construct(SlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
    }

    public function getSlackApi()
    {
        return $this->slackApi;
    }

    public function getUserByName($userName)
    {
        $users = $this->slackApi->usersList();
        $user = array_filter(Util::arrayGet($users, 'members'), function($item) use ($userName) {
            return Util::arrayGet($item, 'name') === $userName;
        });
        return is_array($user) ? (current($user) ?: []) : [];
    }

    public function getUserIdByName($userName, $openChannel = true) {
        $userId = Util::arrayGet($this->getUserByName($userName), 'id');
        if (!$openChannel) {
            return $userId;
        }
        $imData = $this->getSlackApi()->imOpen($userId);
        return Util::arrayGet(Util::arrayGet($imData, 'channel'), 'id');
    }

    public function getChannelByName($channelName)
    {
        $channels = $this->slackApi->channelsList();
        $channel = array_filter(Util::arrayGet($channels, 'channels'), function($item) use ($channelName) {
            return Util::arrayGet($item, 'name') === $channelName;
        });
        return is_array($channel) ? (current($channel) ?: []) : [];

    }

    public function getChannelIdByName($channelName)
    {
        return Util::arrayGet($this->getChannelByName($channelName), 'id');
    }

    public function getGroupByName($groupName)
    {
        $groups = $this->slackApi->groupsList();
        $group = array_filter(Util::arrayGet($groups, 'groups'), function($item) use ($groupName) {
            return Util::arrayGet($item, 'name') === $groupName;
        });
        return is_array($group) ? (current($group) ?: []) : [];

    }

    public function getGroupIdByName($groupName)
    {
        return Util::arrayGet($this->getGroupByName($groupName), 'id');
    }

    public function getRecipientIdByName($name)
    {
        switch ($name[0]) {
            case '@': return $this->getUserIdByName(substr($name, 1));
            case '#': return $this->getChannelIdByName(substr($name, 1));
            default: return $this->getGroupIdByName($name);
        }
    }

    public function getRecipientIdsByNames(array $names) {
        $ids = [];
        foreach ($names as $name) {
            $ids[] = $this->getRecipientIdByName($name);
        }
        return array_unique($ids);
    }

    public function multiSendMessage(array $recipients, $message, $options = [])
    {
        if (count($recipients) === 0) {
            return;
        }
        foreach ($recipients as $recipient) {
            $this->getSlackApi()->chatPostMessage($recipient, $message, $options);
        }
    }

    public function getRecipientUsersIds($channelId)
    {
        switch ($channelId[0]) {
            case '@': return [$this->getUserIdByName(substr($channelId, 1), false)];
            case '#': return $this->getChannelUsersIds(substr($channelId, 1));
            default: return $this->getGroupUsersIds($channelId);
        }

    }

    public function getChannelUsersIds($channelId)
    {
        $channelId = $this->getChannelIdByName($channelId);
        $data = $this->getSlackApi()->channelsInfo($channelId);
        return Util::arrayGet(Util::arrayGet($data, 'channel'), 'members') ?: [];
    }

    public function getGroupUsersIds($groupId)
    {
        $groupId = $this->getGroupIdByName($groupId);
        $data = $this->getSlackApi()->groupsInfo($groupId);
        return Util::arrayGet(Util::arrayGet($data, 'channel'), 'members') ?: [];
    }
}
