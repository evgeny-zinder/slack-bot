<?php

namespace slackbot\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CapMousse\ReactRestify\Runner;
use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Server;

/**
 * Class ServerStartCommand
 * Starts core server
 *
 * @package slackbot\commands
 */
class ApiStubStartCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Console command configuration
     */
    protected function configure()
    {
        $this
            ->setName('stub:api')
            ->setDescription('Starts stub (testing) Slack API server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $port = 8900;
        $server = new Server();

        $server->get('/', function (Request $request, Response $response, $next) {
            $response->writeJson([
                'ok' => true,
                'self' => [
                    'id' => 'U01234567',
                    'name' => 'testbot'
                ],
                'url' => 'wss://localhost:8901/'
            ]);
            $response->end();
        });

        $runner = new Runner($server);
        $runner->listen($port);
    }
}
