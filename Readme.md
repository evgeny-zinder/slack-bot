[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9f062b20-712b-4b26-a0a3-88fc509bb911/mini.png)](https://insight.sensiolabs.com/projects/9f062b20-712b-4b26-a0a3-88fc509bb911)
[![Build Status](https://travis-ci.org/evgeny-zinder/slack-bot.svg?branch=master)](https://travis-ci.org/evgeny-zinder/slack-bot)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/?branch=master)
[![StyleCI](https://styleci.io/repos/46425062/shield)](https://styleci.io/repos/46425062)

# Slack Bot Framewotk

## Intro

This framework is designed for creating Slack chat bots

Framework-based app skeleton: https://github.com/evgeny-zinder/slack-bot-app-skeleton

Features:
 * communicates with Slack using real-time WebSocket connection
 * able to respond (by running callback PHP functions) to any message patterns
 * able to run ansible-style playbooks with extensible set of actions

## Installation

    git clone git@github.com:evgeny-zinder/slack-bot.git
    cd slack-bot
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

## Launching

1. Start server

        php bin/slackbot.php server:start --config=config.yml

2. Start RTM WebSocket listener (if you plan to work with user input)

        php bin/slackbot.php rtm:start --config=config.yml

3. Run playbook

        php bin/slackbot.php playbook:run --playbook=examples/0-simple-message.yml
