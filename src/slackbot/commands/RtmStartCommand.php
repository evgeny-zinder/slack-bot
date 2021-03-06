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
use slackbot\util\Posix;
use eznio\ar\Ar;

/**
 * Class RtmStartCommand
 * Starts RTM WebSocker listener
 *
 * @package slackbot\commands
 */
class RtmStartCommand extends Command
{
    /** @var Config */
    private $config;

    /** @var CurlRequest */
    private $curlRequest;

    /** @var Client */
    private $client;

    /** @var string */
    private $authUrl;

    /** @var string */
    private $socketUrl;

    /** @var string */
    private $serverUrl;

    /** @var string */
    private $rtmUrl;

    /**
     * RtmStartCommand constructor.
     * @param Config $config Config data storage
     * @param CurlRequest $curlRequest cURL interface
     */
    public function __construct(
        Config $config,
        CurlRequest $curlRequest
    ) {
        parent::__construct();
        $this->config = $config;
        $this->curlRequest = $curlRequest;
    }

    /**
     * Console command configuration
     */
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
            )->addOption(
                'rtmUrl',
                null,
                InputOption::VALUE_OPTIONAL,
                'RTM server URL',
                'https://slack.com/api/rtm.start'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->checkPidFile($this->config->getEntry('server.rtmpidfile'))) {
            $output->write('Error: RTM listener is already running, exiting');
            return 1;
        }

        $this->rtmUrl = $input->getOption('rtmUrl');
        $this->authUrl = $this->getAuthUrl();
        $this->socketUrl = $this->getSocketUrl();
        $this->serverUrl = $this->getServerUrl();
        echo "[INFO] Server URL: {$this->serverUrl}\n";
        echo "[INFO] Connecting to RTM URL: {$this->rtmUrl}\n";
        echo "[INFO] Got socket URL: {$this->socketUrl}\n";

        $this->client = $this->createClient();
        $this->processLoop();

        return 0;
    }

    /**
     * @return string
     * @throws \LogicException
     */
    private function getToken()
    {
        $token = $this->config->getEntry('auth.token');
        if (null === $token) {
            throw new \LogicException('No intergration token found in config');
        }
        return $token;
    }

    /**
     * @param string|null $pidFile
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

    /**
     * @return string
     */
    private function getServerUrl()
    {
        $host = $this->config->getEntry('server.host') ?: 'localhost';
        $port = $this->config->getEntry('server.port') ?: '8888';
        return sprintf('http://%s:%s/process/message/', $host, $port);
    }

    /**
     * @return string
     */
    protected function getSocketUrl()
    {
        $result = $this->curlRequest->getCurlResult($this->authUrl);
        $result = json_decode($result['body'], true);
        if (true !== Ar::get($result, 'ok')) {
            echo 'Error connecting to Slack WebSocket: ' . Ar::get($result, 'error');
            exit(1);
        }
        $socketUrl = $result['url'];
        return $socketUrl;
    }

    /**
     * @return Client
     */
    protected function createClient()
    {
        $client = new Client($this->socketUrl);
        $client->setTimeout(86400 * 1000);
        return $client;
    }

    /**
     * @return string
     */
    protected function getAuthUrl()
    {
        $token = $this->getToken();
        $urlParams = [
            'token' => $token
        ];
        $authUrl = $this->rtmUrl . '?' . http_build_query($urlParams);
        return $authUrl;
    }

    /**
     * Main execution loop
     * @throws \Exception
     */
    protected function processLoop()
    {
        while (true) {
            try {
                $data = $this->client->receive();

                $parsedData = json_decode($data, true);
                if (
                    'message' === Ar::get($parsedData, 'type')
                    && 'bot_message' !== Ar::get($parsedData, 'subtype')
                ) {
                    echo sprintf(
                        "[INFO] Got message: '%s' from %s in %s\n",
                        Ar::get($parsedData, 'text') ?: '<nothing>',
                        Ar::get($parsedData, 'user') ?: 'bot',
                        Ar::get($parsedData, 'channel') ?: 'unknown channel'
                    );
                    try {
                        $this->curlRequest->getCurlResult(
                            $this->serverUrl,
                            [
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'message' => $data
                                ]
                            ]
                        );
                    } catch (\Exception $e) {
                    }
                }
            } catch (Exception $e) {
                $result = $this->curlRequest->getCurlResult($this->authUrl);
                $result = json_decode($result['body'], true);
                $this->socketUrl = $result['url'];
                $this->client = $this->createClient();
            }
        }
    }
}
