<?php

namespace slackbot\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerStartCommand
 * Starts core server
 *
 * @package slackbot\commands
 */
class RtmStubStartCommand extends Command
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
            ->setName('stub:rtm')
            ->setDescription('Starts stub (testing) Slack RTM server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $server = new \WebSocket\Server(['timeout' => 200, 'port' => 8901]);
        echo $server->getPort(), "\n";

        while ($connection = $server->accept()) {
            echo "Connected!\n";
            while(1) {
                $message = $server->receive();
                echo "Received $message\n\n";
                
                // //{"type":"message","channel":"D0Y7J3MHT","user":"U0BLLPJP8","text":"!test","ts":"1476761016.000065","team":"T0BLLQ1M3"}
                $server->send(json_encode([
                    'type' => 'message',
                    'channel' => 'D01234567',
                    'user' => 'U76543210',
                    'text' => '!test',
                    'ts' => '1476761016.000065',
                    'team' => 'T01234567'
                ]));
                sleep(5);
            }
        }

    }
}
