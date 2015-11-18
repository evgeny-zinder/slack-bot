<?php

namespace slackbot\commands;

use slackbot\models\Registry;
use slackbot\util\Posix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CapMousse\ReactRestify\Runner;
use slackbot\CoreBuilder;
use slackbot\models\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Parser;

class ServerStartCommand extends Command
{
    const PID_FILE = 'var/core.pid';
    const DEFAULT_PORT = 8888;

    protected function configure()
    {
        $this
            ->setName('server:start')
            ->setDescription('Starts core slackbot server')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_OPTIONAL,
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

        $builder = new CoreBuilder();

        $server = $this->buildServer($builder);
        $this->runServer($server);
    }

    /**
     * @return bool
     */
    protected function checkPidFile()
    {
        if (file_exists(self::PID_FILE)) {
            $pid = file_get_contents(self::PID_FILE);
            if (Posix::isPidActive($pid)) {
                return false;
            }
            unlink(self::PID_FILE);
        }
        file_put_contents(self::PID_FILE, getmypid());
        return true;
    }

    public function buildContainer(CoreBuilder $builder, $configPath = null)
    {
        $config = new Config(new Parser());
        if (file_exists($configPath)) {
            $config->loadData($configPath);
        }

        return $builder->buildContainer($config);
    }

    /**
     * @return \CapMousse\ReactRestify\Server
     */
    protected function buildServer(CoreBuilder $builder)
    {
        $server = $builder->buildServer();
        return $server;
    }

    /**
     * @param $server
     */
    protected function runServer($server)
    {
        $runner = new Runner($server);
        $runner->listen(
            Registry::get('container')['config']->getEntry('server.port') ?: self::DEFAULT_PORT
        );
    }
}
