<?php

namespace slackbot\commands;

use slackbot\Util;
use slackbot\util\FileLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use slackbot\util\CurlRequest;
use Cron\CronExpression;

/**
 * Class CronWorkerCommand
 * Cron worker to be running by system cron every minute:
 *     * * * * * php bin/slackbot.php cron:run --host=localhost --port=8888
 *
 * @package slackbot\commands
 */
class CronWorkerCommand extends Command
{
    /** @var CurlRequest */
    private $curlRequest;

    /** @var CronExpression */
    private $cronExpression;

    /** @var FileLoader */
    private $fileLoader;

    /** @var string */
    private $logPath;

    /**
     * CronWorkerCommand constructor.
     * @param CurlRequest $curlRequest cURL interface
     * @param CronExpression $cronExpression Cron expression parser & evaluator
     * @param FileLoader $fileLoader File loading interface
     */
    public function __construct(
        CurlRequest $curlRequest,
        CronExpression $cronExpression,
        FileLoader $fileLoader
    ) {
        parent::__construct();
        $this->curlRequest = $curlRequest;
        $this->cronExpression = $cronExpression;
        $this->fileLoader = $fileLoader;
    }

    /**
     * Console command configuration
     */
    protected function configure()
    {
        $this
            ->setName('cron:run')
            ->setDescription('SlackBot cron worker')
            ->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Slackbot host address',
                'localhost'
            )->addOption(
                'port',
                null,
                InputOption::VALUE_OPTIONAL,
                'Slackbot port',
                '8888'
            )->addOption(
                'log',
                null,
                InputOption::VALUE_OPTIONAL,
                'Log file path',
                null
            )
            ->ignoreValidationErrors();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->log('Cron worker started');
        $this->logPath = $input->getOption('log');

        $cronInfoUrl = sprintf(
            'http://%s:%d/info/cron/',
            $input->getOption('host'),
            $input->getOption('port')
        );
        $this->log('Core URL: ' . $cronInfoUrl);

        $response = json_decode($this->curlRequest->getCurlResult(
            $cronInfoUrl,
            [
                CURLOPT_TIMEOUT_MS => 100000
            ]
        )['body'], true);
        if (!is_array($response)) {
            $this->log('Core connection failed, exiting');
            throw new \RuntimeException('Error connecting to core server');
        }

        foreach ($response as $cronItem) {
            $this->cronExpression->setExpression(Util::arrayGet($cronItem, 'time'));
            $this->log('Checking element: ' . Util::arrayGet($cronItem, 'time'));
            if ($this->cronExpression->isDue()) {
                $this->log('Allowed for execution, type: ' . Util::arrayGet($cronItem, 'type'));
                switch(Util::arrayGet($cronItem, 'type'))
                {
                    case 'playbook':
                        $url = sprintf(
                            'http://%s:%d/playbook/run/',
                            $input->getOption('host'),
                            $input->getOption('port')
                        );

                        $playbookFile = Util::arrayGet($cronItem, 'playbook');
                        $playbook = $this->fileLoader->load($playbookFile);

                        $this->curlRequest->getCurlResult(
                            $url,
                            [
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'playbook' => urlencode($playbook),
                                    'filename' => $playbookFile
                                ],
                                CURLOPT_TIMEOUT_MS => 100000
                            ]
                        );
                        $this->log('Executed playbook: ' . $playbookFile);
                        break;

                    case 'command':
                        $url = sprintf(
                            'http://%s:%d/command/run/',
                            $input->getOption('host'),
                            $input->getOption('port')
                        );

                        $command = Util::arrayGet($cronItem, 'command');
                        if (null === $command) {
                            break;
                        }

                        $this->curlRequest->getCurlResult(
                            $url,
                            [
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'command' => urlencode($command)
                                ],
                                CURLOPT_TIMEOUT_MS => 100000
                            ]
                        );
                        $this->log('Executed command: ' . $command);
                        break;

                    case 'curl':
                        break;
                }

            }
        }
        $this->log('Cron worker finished');
    }

    private function log($data)
    {
        if (null === $this->logPath) {
            return;
        }
        if (!file_exists($this->logPath) || !is_readable($this->logPath)) {
            return;
        }
        $fid = fopen($this->logPath, 'a');
        fputs($fid, sprintf('[%s] %s', date('Y-m-d H:i:s', time()), $data));
        fclose($fid);
    }
}
