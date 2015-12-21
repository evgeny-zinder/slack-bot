<?php

namespace slackbot\commands;

use slackbot\util\Posix;
use slackbot\models\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends Command
{
    /** @var Config */
    private $config;

    /**
     * RtmStopCommand constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure()
    {
        $this
            ->setName('server:stop')
            ->setDescription('Stops core slackbot server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $this->config->getEntry($this->config->getEntry('server.pidfile'));
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (Posix::isPidActive($pid)) {
                posix_kill($pid, SIGINT);
                echo 'Slackbot server stopped';
            } else {
                echo 'Slackbot server is not running';
            }
            unlink($pidFile);
        } else {
            echo 'Slackbot server is not running';
        }
    }
}
