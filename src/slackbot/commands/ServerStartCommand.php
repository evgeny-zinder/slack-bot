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
use slackbot\models\Config;
use slackbot\models\ArgvParser;

/**
 * Class ServerStartCommand
 * Starts core server
 *
 * @package slackbot\commands
 */
class ServerStartCommand extends Command
{
    /** @var Config */
    private $config;

    /** @var ArgvParser */
    private $argParser;


    public function __construct(Config $config, ArgvParser $argvParser)
    {
        parent::__construct();
        $this->config = $config;
        $this->argParser = $argvParser;
    }

    /**
     * Console command configuration
     */
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
        $builder = new CoreBuilder($this->config, $this->argParser);

        /** @var \CapMousse\ReactRestify\Server $server */
        $server = $builder->buildServer();
        $this->runServer($server);
    }

    /**
     * Checks if another server process with the same config is running
     *
     * @return bool
     */
    protected function checkPidFile()
    {
        $config = Registry::get('container')['config'];
        $pidFile = $config->getEntry('server.pidfile');
        if (null === $pidFile) {
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
     * @param \CapMousse\ReactRestify\Server $server \CapMousse\ReactRestify\Server server instance to run
     */
    protected function runServer($server)
    {
        $port = Registry::get('container')['config']->getEntry('server.port');
        if (null === $port) {
            throw new \RuntimeException('server.port value should be set in config');
        }

        $runner = new Runner($server);
        $runner->listen($port);
    }
}
