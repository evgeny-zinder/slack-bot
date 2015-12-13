<?php

namespace slackbot\commands;

use slackbot\models\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use slackbot\models\VariablesPlacer;
use slackbot\util\CurlRequest;

class PlaybookRunCommand extends Command
{
    /** @var VariablesPlacer */
    private $variablesPlacer;

    /** @var CurlRequest */
    private $curlRequest;

    public function __construct(CurlRequest $curlRequest, VariablesPlacer $variablesPlacer)
    {
        parent::__construct();
        $this->curlRequest = $curlRequest;
        $this->variablesPlacer = $variablesPlacer;
    }

    protected function configure()
    {
        $this
            ->setName('playbook:run')
            ->setDescription('Runs playbook on slackbot server')
            ->addOption(
                'playbook',
                null,
                InputOption::VALUE_REQUIRED,
                'Playbook to run'
            )->addOption(
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
        $playbookFile = $input->getOption('playbook');
        if (!file_exists($playbookFile)) {
            throw new \RuntimeException('Playbook file not found');
        }

        $playbook = file_get_contents($playbookFile);

        /** @var \slackbot\models\VariablesPlacer $variablesPlacer */
        $this->variablesPlacer->setVars(Registry::get('container')['argv_parser']->all());
        $this->variablesPlacer->setText($playbook);
        $playbook = $variablesPlacer->place();

        $url = sprintf(
            'http://%s:%d/playbook/run/',
            $input->getOption('host'),
            $input->getOption('port')
        );

        $response = $this->curlRequest->getCurlResult(
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

        echo $response['body'];
    }
}
