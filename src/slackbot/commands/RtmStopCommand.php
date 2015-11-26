<?php

namespace slackbot\commands;

use slackbot\util\Posix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RtmStopCommand extends Command
{
    const PID_FILE = 'var/rtm.pid';

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
        if (file_exists(self::PID_FILE)) {
            $pid = file_get_contents(self::PID_FILE);
            if (Posix::isPidActive($pid)) {
                posix_kill($pid, SIGINT);
                echo 'RTM listener stopped';
            } else {
                echo 'RTM listener is not running';
            }
            unlink(self::PID_FILE);
        } else {
            echo 'RTM listener is not running';
        }
    }
}
