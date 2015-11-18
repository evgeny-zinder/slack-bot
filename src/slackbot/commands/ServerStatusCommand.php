<?php

namespace slackbot\commands;

use slackbot\util\Posix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStatusCommand extends Command
{
    const PID_FILE = 'var/core.pid';

    protected function configure()
    {
        $this
            ->setName('server:status')
            ->setDescription('Get core slackbot server status');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists(self::PID_FILE)) {
            $pid = file_get_contents(self::PID_FILE);
            if (Posix::isPidActive($pid)) {
                echo 'Slackbot server is running';
            } else {
                echo 'Slackbot server is not running';
            }
        } else {
            echo 'Slackbot server is not running';
        }
    }
}
