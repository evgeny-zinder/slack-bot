<?php

chdir(__DIR__ . '/../');

$autoloadFiles = [__DIR__ . '/../vendor/autoload.php',  __DIR__ . '/../../../autoload.php'];
foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
    }
}

$app = new \slackbot\Application($argv);
$app->bootstrap();
$app->run();
