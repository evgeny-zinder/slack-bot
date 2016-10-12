<?php

namespace slackbot\models;

use eznio\ar\Ar;

/**
 * Class SlackFacade
 * Slack API high-level adapter
 * @package slackbot\models
 */
class SlackFacade
{
    /** @var SlackApi */
    private $slackApi;

    /**
     * SlackFacade constructor.
     * @param SlackApi $slackApi
     */
    public function __construct(SlackApi $slackApi)
    {
        $this->slackApi = $slackApi;
    }

    /**
     * @return SlackApi
     */
    public function getSlackApi()
    {
        return $this->slackApi;
    }

    /**
     * Returns user info by its name
     * @param string $userName
     * @return array
     */
    public function getUserByName($userName)
    {
        $users = $this->slackApi->usersList();
        $user = array_filter(Ar::get($users, 'members'), function ($item) use ($userName) {
            return $userName === Ar::get($item, 'name');
        });
        return is_array($user) ? (current($user) ?: []) : [];
    }

    /**
     * Returns user ID (or user DM ID) by user name
     * @param string $userName
     * @param bool $shouldOpenImChannel return DM channel ID instead of user ID
     * @return string
     */
    public function getUserIdByName($userName, $shouldOpenImChannel = true)
    {
        $userId = Ar::get($this->getUserByName($userName), 'id');
        if (null === $userId || !$shouldOpenImChannel) {
            return $userId;
        }
        $imData = $this->getSlackApi()->imOpen($userId);
        return Ar::get(Ar::get($imData, 'channel'), 'id');
    }

    /**
     * Returns user info by ID
     * @param string $userId
     * @return array|mixed
     */
    public function getUserInfoById($userId)
    {
        $data = $this->slackApi->usersInfo($userId);
        return true === Ar::get($data, 'ok') ? Ar::get($data, 'user') : [];
    }

    /**
     * Returns user name by ID
     * @param string $userId
     * @return null|string
     */
    public function getUserNameById($userId)
    {
        $name = Ar::get($this->getUserInfoById($userId), 'name');
        return null !== $name ? '@' . $name : null;
    }

    /**
     * Returns public channel info by its name
     * @param string $channelName
     * @return array
     */
    public function getChannelByName($channelName)
    {
        $channels = $this->slackApi->channelsList();
        $channel = array_filter(Ar::get($channels, 'channels'), function ($item) use ($channelName) {
            return Ar::get($item, 'name') === $channelName;
        });
        return is_array($channel) ? (current($channel) ?: []) : [];

    }

    /**
     * Returns public channel ID by its name
     * @param $channelName
     * @return string|null
     */
    public function getChannelIdByName($channelName)
    {
        return Ar::get($this->getChannelByName($channelName), 'id');
    }

    /**
     * Returns private group info by its name
     * @param string $groupName
     * @return array
     */
    public function getGroupByName($groupName)
    {
        $groups = $this->slackApi->groupsList();
        $group = array_filter(Ar::get($groups, 'groups'), function ($item) use ($groupName) {
            return  $groupName === Ar::get($item, 'name');
        });
        return is_array($group) ? (current($group) ?: []) : [];

    }

    /**
     * Returns private group ID by its name
     * @param string $groupName
     * @return string
     */
    public function getGroupIdByName($groupName)
    {
        return Ar::get($this->getGroupByName($groupName), 'id');
    }

    /**
     * Returns recipient (user/channel/group) ID by its name
     * @param string $name
     * @return string|null
     */
    public function getRecipientIdByName($name)
    {
        if (!is_string($name)) {
            return null;
        }

        if ('<' === $name[0]) {
            return preg_replace('/[\<\>\#\@]*/', '', $name);
        }

        switch ($name[0]) {
            case '@':
                return $this->getUserIdByName(substr($name, 1));

            case '#':
                return $this->getChannelIdByName(substr($name, 1));

            default:
                return $this->getGroupIdByName($name);
        }
    }

    /**
     * Returns multiple recipient IDs by their names
     * @param array $names
     * @return array
     */
    public function getRecipientIdsByNames(array $names)
    {
        $ids = [];
        foreach ($names as $name) {
            $id = $this->getRecipientIdByName($name);
            if (null === $id) {
                continue;
            }
            $ids[] = $id;
        }
        return array_unique($ids);
    }

    /**
     * Send message to multiple recipients
     * @param array $recipients recipients names
     * @param string $message message to send
     * @param array $options slack formatting options
     */
    public function multiSendMessage(array $recipients, $message, $options = [])
    {
        if (0 === count($recipients)) {
            return;
        }
        foreach ($recipients as $recipient) {
            $this->getSlackApi()->chatPostMessage($recipient, $message, $options);
        }
    }

    /**
     * Returns list of recipient (channel/group/user) user IDs
     * @param $recipientId
     * @return array
     */
    public function getRecipientUsersIds($recipientId)
    {
        if (!is_string($recipientId)) {
            return [];
        }

        switch ($recipientId[0]) {
            case '@':
                return null !== ($userId = $this->getUserIdByName(substr($recipientId, 1), false))
                    ? [$userId] : [];

            case '#':
                return $this->getChannelUsersIds(substr($recipientId, 1));

            default:
                return $this->getGroupUsersIds($recipientId);
        }

    }

    /**
     * Returns list of channel user IDs
     * @param string $channelId
     * @return array
     */
    public function getChannelUsersIds($channelId)
    {
        $channelId = $this->getChannelIdByName($channelId);
        if (null === $channelId) {
            return [];
        }
        $data = $this->getSlackApi()->channelsInfo($channelId);
        return Ar::get(Ar::get($data, 'channel'), 'members') ?: [];
    }

    /**
     * Returns list of group user IDs
     * @param string $groupId
     * @return array
     */
    public function getGroupUsersIds($groupId)
    {
        $groupId = $this->getGroupIdByName($groupId);
        if (null === $groupId) {
            return [];
        }
        $data = $this->getSlackApi()->groupsInfo($groupId);
        return Ar::get($data, 'group.members') ?: [];
    }
}
