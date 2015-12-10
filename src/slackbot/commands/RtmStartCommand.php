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
use slackbot\util\Posix;
use slackbot\models\Registry;

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
        $config = Registry::get('container')['config'];
        if (!$this->checkPidFile($config->getEntry('server.rtmpidfile'))) {
            $output->write('Error: RTM listener is already running, exiting');
            return;
        }

        $token = $this->getToken();
        $urlParams = [
            'token' => $token
        ];
        $authUrl = self::BASE_URL . '?' . http_build_query($urlParams);

        $serverUrl = $this->getServerUrl();
        echo "[DEBUG] Server URL: {$serverUrl}\n";

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
                            $serverUrl,
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
        $token = $this->config->getEntry('auth.token');
        if ($token === null) {
            throw new \LogicException('No intergration token found in config');
        }
        return $token;
    }

    /**
     * @return bool
     */
    private function checkPidFile($pidFile)
    {
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

    private function getServerUrl()
    {
        $host = $this->config->getEntry('server.host') ?: 'localhost';
        $port = $this->config->getEntry('server.port') ?: '8888';
        return sprintf('http://%s:%s/process/message/', $host, $port);
    }
}
