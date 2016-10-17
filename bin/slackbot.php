<?php

chdir(__DIR__ . '/../');

$autoloadFiles = [__DIR__ . '/../vendor/autoload.php',  __DIR__ . '/../../../autoload.php'];
foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
    }
}

try {
    $app = new \slackbot\Application($argv);
    $app->bootstrap();
    $app->run();
} catch (\Exception $e) {
    echo sprintf(
        "EXCEPTION!\n%s:%s - %s\n",
        $e->getFile(),
        $e->getLine(),
        $e->getMessage()
    );

    echo $e->getTraceAsString();
}
