<?php

namespace slackbot\commands;

use slackbot\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use slackbot\util\CurlRequest;
use Cron\CronExpression;

class CronWorkerCommand extends Command
{
    /** @var CurlRequest */
    private $curlRequest;

    /** @var CronExpression */
    private $cronExpression;

    public function __construct(CurlRequest $curlRequest, CronExpression $cronExpression)
    {
        parent::__construct();
        $this->curlRequest = $curlRequest;
        $this->cronExpression = $cronExpression;
    }

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
        if (!is_array($response) || count($response) === 0) {
            return;
        }

        foreach ($response as $cronItem) {
            $this->cronExpression->setExpression(Util::arrayGet($cronItem, 'time'));
            if ($this->cronExpression->isDue()) {
                $playbookFile = Util::arrayGet($cronItem, 'playbook');
                if (!file_exists($playbookFile)) {
                    throw new \RuntimeException('Can\'t load playbook for cron execution: ' . $playbookFile);
                }

                $playbook = file_get_contents($playbookFile);

                $url = sprintf(
                    'http://%s:%d/playbook/run/',
                    $input->getOption('host'),
                    $input->getOption('port')
                );

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
            }
        }

    }
}
