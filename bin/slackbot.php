<?php

require realpath(__DIR__ . '/../vendor/autoload.php');

use slackbot\models\Registry;

$config = new \slackbot\models\Config(
    new \Symfony\Component\Yaml\Parser(),
    new \slackbot\util\FileLoader()
);

if (is_array($argv) && count($argv)) {
    foreach ($argv as $args) {
        preg_match('/--config\=(.+)/', $args, $matches);
        if (count($matches) === 2) {
            $config->loadData($matches[1]);
        }
    }
}

$coreBuilder = new \slackbot\CoreBuilder();
$container = $coreBuilder->buildContainer($config);
Registry::set('container', $container);

$app = new \Symfony\Component\Console\Application('CMS Slack Bot', '@package_version@');

$app->add(new \slackbot\commands\ServerStartCommand());
$app->add(new \slackbot\commands\ServerStopCommand());
$app->add(new \slackbot\commands\ServerStatusCommand());
$app->add(new \slackbot\commands\PlaybookRunCommand());
$app->add(new \slackbot\commands\RtmStartCommand(
    $config,
    $container['curl_request']
));

$app->run();
