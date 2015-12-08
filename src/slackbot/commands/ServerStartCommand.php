<?php

namespace slackbot\commands;

use slackbot\models\Registry;
use slackbot\util\Posix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CapMousse\ReactRestify\Runner;
use slackbot\CoreBuilder;
use Symfony\Component\Console\Input\InputOption;

class ServerStartCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:start')
            ->setDescription('Starts core slackbot server')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Config file location'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->checkPidFile()) {
            $output->write('Error: server is already running, exiting');
            return;
        }

        /** @var CoreBuilder $builder */
        $builder = new CoreBuilder(
            Registry::get('container')['config'],
            Registry::get('container')['arg_parser`']
        );
        $server = $builder->buildServer();
        $this->runServer($server);
    }

    /**
     * @return bool
     */
    protected function checkPidFile()
    {
        $config = Registry::get('container')['config'];
        $pidFile = $config->getEntry('server.pidfile');
        if ($pidFile === null) {
            throw new \RuntimeException('server.pidfile value should be set in config');
        }

        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (Posix::isPidActive($pid)) {
                return false;
            }
            unlink($pidFile);
        }
        file_put_contents($pidFile, getmypid());
        return true;
    }

    /**
     * @param $server
     */
    protected function runServer($server)
    {
        $port = Registry::get('container')['config']->getEntry('server.port');
        if ($port === null) {
            throw new \RuntimeException('server.port value should be set in config');
        }

        $runner = new Runner($server);
        $runner->listen($port);
    }
}
