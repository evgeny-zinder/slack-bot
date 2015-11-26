<?php

namespace slackbot;

use slackbot\models\Registry;
use Symfony\Component\Yaml\Exception\RuntimeException;

class Application
{
    /** @var \Symfony\Component\Console\Application */
    protected $app;

    /** @var models\Config */
    protected $config;

    /** @var \Pimple\Container */
    protected $container;

    public function __construct($argv)
    {
        $this->config = new \slackbot\models\Config(
            new \Symfony\Component\Yaml\Parser(),
            new \slackbot\util\FileLoader()
        );

        if (is_array($argv) && count($argv)) {
            foreach ($argv as $args) {
                preg_match('/--config\=(.+)/', $args, $matches);
                if (count($matches) === 2) {
                    if (file_exists($matches[1]) && is_readable($matches[1])){
                        $this->config->loadData($matches[1]);
                    } else {
                        throw new RuntimeException('Config file not accessible');
                    }
                }
            }
        }

    }

    public function bootstrap()
    {
        $coreBuilder = new \slackbot\CoreBuilder();
        $this->container = $coreBuilder->buildContainer($this->config);
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
