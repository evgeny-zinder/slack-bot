[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cd9f9f99-81e9-4ed2-9102-85cb369c8dd7/mini.png)](https://insight.sensiolabs.com/projects/cd9f9f99-81e9-4ed2-9102-85cb369c8dd7)
[![Build Status](https://travis-ci.org/evgeny-zinder/slack-bot.svg?branch=master)](https://travis-ci.org/evgeny-zinder/slack-bot)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/evgeny-zinder/slack-bot/?branch=master)
[![StyleCI](https://styleci.io/repos/45424393/shield)](https://styleci.io/repos/45424393)

# Slack Bot Framewotk

## Intro

This framework is designed for creating bots for Slack chat

Features:
 * communicates with Slack using real-time WebSocket connection
 * able to respond to any message patterns
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

## Roadmap

### Core development
1. [X] Refactor bot to use react engine
2. [X] Make all existing commands run on new engine
3. [ ] Add validation chains to config/playbook entries
4. [X] Add config overwrite ability to playbooks
5. [X] Implement InputManager with timed message subscriptions
6. [X] Implement OutputManager wih RTM and API support and RTM priority
7. [ ] Implement webhook route
8. [ ] Implement slash command route
9. [ ] Implement cron subsystem
10. [X] Implement internal queue and user input based on it
11. [ ] Add variables support to playbooks. Vars should be passed from command line
12. [X] !-commands interface and demo implementation
13. [ ] Message formatting helper
14. [ ] Unit tests coverage
15. [ ] Refactor config, remove unused auth data
16. [ ] Start using bot presence config section

### Actions
1. [X] user input
2. [X] if / then / else
3. [X] ACL for commands
4. [X] next loop iteration
5. [X] exit loop
6. [ ] stop playbook execution


### Unit tests coverage
1. [ ] models
    * [X] ConditionResolver
    * [ ] Config
    * [ ] Registry
    * [ ] SlackApi
    * [ ] SlackFacade
    * [ ] Variables
2. [ ] utils
    * [ ] Posix
    * [X] CurlRequest
    * [ ] PostParser
    * [ ] RecipientParser
3. [ ] CoreBuilder
4. [ ] CoreProcessor
5. [ ] OutputManager
6. [ ] PlaybookExecutor
7. [ ] handlers\actions
    * [ ] Base
    * [ ] Break
    * [ ] Continue
    * [ ] If
    * [ ] Loop
    * [ ] SendMessage
    * [ ] SetVariable
    * [ ] UserInput
8. [ ] handlers\commands
    * [ ] Base
    * [ ] GitLog
9. [ ] handlers\requests
    * [ ] Base


