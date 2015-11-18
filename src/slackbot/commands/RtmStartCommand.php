<?php

namespace slackbot\commands;

use slackbot\models\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebSocket\Client;
use slackbot\util\CurlRequest;
use WebSocket\Exception;
use Symfony\Component\Console\Input\InputOption;
use slackbot\Util;

/**
 * Class RtmStartCommand
 * @package slackbot\commands
 */
class RtmStartCommand extends Command
{
    const BASE_URL = 'https://slack.com/api/rtm.start';

    /** @var Config */
    private $config;

    /** @var CurlRequest */
    private $curlRequest;

    public function __construct(
        Config $config,
        CurlRequest $curlRequest
    ) {
        parent::__construct();
        $this->config = $config;
        $this->curlRequest = $curlRequest;
    }

    protected function configure()
    {
        $this
            ->setName('rtm:start')
            ->setDescription('Starts WebSocket RTM listener')
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
        $token = $this->getToken();
        $urlParams = [
            'token' => $token
        ];
        $authUrl = self::BASE_URL . '?' . http_build_query($urlParams);

        $result = $this->curlRequest->getCurlResult($authUrl);
        $result = json_decode($result['body'], true);
        $socketUrl = $result['url'];

        $client = new Client($socketUrl);
        $client->setTimeout(86400 * 1000);
        while (1) {
            try {
                $data = $client->receive();

                $parsedData = json_decode($data, true);
                if ($parsedData['type'] == 'message') {
                    echo sprintf(
                        "[INFO] Got message: '%s' from %s in %s\n",
                        $parsedData['text'],
                        Util::arrayGet($parsedData, 'user') ?: 'bot',
                        Util::arrayGet($parsedData, 'channel') ?: 'unknown channel'
                    );
                    try {
                        $this->curlRequest->getCurlResult(
                            'http://localhost:8888/process/message/',
                            [
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'message' => $data
                                ]
                            ]
                        );
                    }
                    catch (\Exception $e) {}
                }
            } catch (Exception $e) {
                $result = $this->curlRequest->getCurlResult($authUrl);
                $result = json_decode($result['body'], true);
                $socketUrl = $result['url'];
                $client = new Client($socketUrl);
                $client->setTimeout(86400 * 1000);
            }
        }
    }

    private function getToken()
    {
        $token = $this->config->getEntryFromArray('send', 'type=rtm', 'token');
        if ($token === null) {
            throw new \LogicException('No valid RTM config entries found');
        }
        return $token;
    }
}
