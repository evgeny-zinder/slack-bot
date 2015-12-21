<?php

namespace slackbot;

use slackbot\models\ArgvParser;
use slackbot\models\Registry;
use slackbot\models\Config;
use Symfony\Component\Yaml\Parser;
use slackbot\util\FileLoader;
use slackbot\commands as commands;
use Symfony\Component\Console\Application as ConsoleApp;

/**
 * Class Application
 * Main core server class
 * @package slackbot
 */
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

    /**
     * Application constructor.
     * @param $argv array CLI arguments
     */
    public function __construct($argv)
    {
        $this->config = new Config(
            new Parser(),
            new FileLoader()
        );

        $this->argParser = new ArgvParser($argv);

        $configFile = $this->argParser->get('config');
        if (file_exists($configFile) && is_readable($configFile)) {
            $this->config->loadData($configFile);
        }
    }

    /**
     * Configures console application
     */
    public function bootstrap()
    {
        $coreBuilder = new CoreBuilder();
        $this->container = $coreBuilder->buildContainer(
            $this->config,
            $this->argParser
        );
        Registry::set('container', $this->container);

        $this->app = new ConsoleApp('CMS Slack Bot', '@package_version@');

        $this->app->add(new commands\ServerStartCommand(
            $this->container['config'],
            $this->container['argv_parser']
        ));
        $this->app->add(new commands\ServerStopCommand(
            $this->container['config']
        ));
        $this->app->add(new commands\ServerStatusCommand(
            $this->container['config']
        ));
        $this->app->add(new commands\PlaybookRunCommand(
            $this->container['curl_request'],
            $this->container['variables_placer'],
            $this->container['file_loader']
        ));
        $this->app->add(new commands\RtmStartCommand(
            $this->config,
            $this->container['curl_request']
        ));
        $this->app->add(new commands\RtmStopCommand(
            $this->container['config']
        ));
        $this->app->add(new commands\CronWorkerCommand(
            $this->container['curl_request'],
            $this->container['cron_expression'],
            $this->container['file_loader']
        ));

    }

    /**
     * Runs the main loop
     * @throws \Exception
     */
    public function run() {
        $this->app->run();
    }

    /**
     * @return ConsoleApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return \Pimple\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
