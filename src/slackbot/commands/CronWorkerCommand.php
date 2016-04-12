<?php

namespace slackbot\commands;

use slackbot\CoreProcessor;
use slackbot\dto\RequestDto;
use slackbot\models\Registry;
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
            )->ignoreValidationErrors();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cronInfoUrl = sprintf(
            'http://%s:%d/info/cron/',
            $input->getOption('host'),
            $input->getOption('port')
        );

        $response = json_decode($this->curlRequest->getCurlResult(
            $cronInfoUrl,
            [
                CURLOPT_TIMEOUT_MS => 100000
            ]
        )['body'], true);
        if (!is_array($response)) {
            throw new \RuntimeException('Error connecting to core server');
        }

        foreach ($response as $cronItem) {
            $this->cronExpression->setExpression(Util::arrayGet($cronItem, 'time'));
            if ($this->cronExpression->isDue()) {
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
                        break;

                    case 'curl':
                        break;
                }

            }
        }

    }
}
