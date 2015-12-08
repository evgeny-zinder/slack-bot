<?php

namespace slackbot;

use slackbot\models\ArgvParser;
use slackbot\models\Registry;

class Application
{
    /** @var \Symfony\Component\Console\Application */
    protected $app;

    /** @var models\Config */
    protected $config;

    /** @var \Pimple\Container */
    protected $container;

    /** @var models\ArgvParser */
    protected $argParser;

    public function __construct($argv)
    {
        $this->config = new \slackbot\models\Config(
            new \Symfony\Component\Yaml\Parser(),
            new \slackbot\util\FileLoader()
        );

        $this->argParser = new ArgvParser($argv);

        $configFile = $this->argParser->get('config');
        if (file_exists($configFile) && is_readable($configFile)) {
            $this->config->loadData($configFile);
        }
    }

    public function bootstrap()
    {
        $coreBuilder = new \slackbot\CoreBuilder();
        $this->container = $coreBuilder->buildContainer(
            $this->config,
            $this->argParser
        );
        Registry::set('container', $this->container);

        $this->app = new \Symfony\Component\Console\Application('CMS Slack Bot', '@package_version@');

        $this->app->add(new \slackbot\commands\ServerStartCommand());
        $this->app->add(new \slackbot\commands\ServerStopCommand());
        $this->app->add(new \slackbot\commands\ServerStatusCommand());
        $this->app->add(new \slackbot\commands\PlaybookRunCommand());
        $this->app->add(new \slackbot\commands\RtmStartCommand(
            $this->config,
            $this->container['curl_request']
        ));
        $this->app->add(new \slackbot\commands\RtmStopCommand());
    }

    public function run() {
        $this->app->run();
    }

    public function getApp()
    {
        return $this->app;
    }

    public function getContainer()
    {
        return $this->container;
    }
}
