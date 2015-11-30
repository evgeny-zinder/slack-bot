<?php

namespace slackbot\commands;

use slackbot\CoreBuilder;
use slackbot\models\ArgvParser;
use slackbot\models\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class PlaybookRunCommand extends Command
{
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
        $variablesPlacer = Registry::get('container')['variables_placer'];
        $variablesPlacer->setVars(Registry::get('container')['argv_parser']->all());
        $variablesPlacer->setText($playbook);
        $playbook = $variablesPlacer->place();

        $url = sprintf(
            'http://%s:%d/playbook/run/',
            $input->getOption('host'),
            $input->getOption('port')
        );

        $curlRequest = Registry::get('container')['curl_request'];
        $response = $curlRequest->getCurlResult(
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
