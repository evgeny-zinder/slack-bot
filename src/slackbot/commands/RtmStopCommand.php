<?php

namespace slackbot\commands;

use slackbot\models\Config;
use slackbot\util\Posix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RtmStopCommand
 * Stops RTM WebSocker listener
 *
 * @package slackbot\commands
 */
class RtmStopCommand extends Command
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

    /**
     * Console command configuration
     */
    protected function configure()
    {
        $this
            ->setName('rtm:stop')
            ->setDescription('Stops slackbot RTM listener');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile = $this->config->getEntry($this->config->getEntry('server.rtmpidfile'));
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (Posix::isPidActive($pid)) {
                posix_kill($pid, SIGINT);
                echo 'RTM listener stopped';
            } else {
                echo 'RTM listener is not running';
            }
            unlink($pidFile);
        } else {
            echo 'RTM listener is not running';
        }
    }
}
