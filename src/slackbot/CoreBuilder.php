<?php

namespace slackbot;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Server;
use eznio\ar\Ar;
use Pimple\Container;
use slackbot\caching\ApiCache;
use slackbot\caching\storages\MemoryStorage;
use slackbot\dto\RequestDto;
use slackbot\handlers\action\ContinueActionHandler;
use slackbot\handlers\action\BreakActionHandler;
use slackbot\handlers\action\IfActionHandler;
use slackbot\handlers\action\LoopActionHandler;
use slackbot\handlers\action\RunCommandActionHandler;
use slackbot\handlers\action\SendMessageActionHandler;
use slackbot\handlers\action\SetVariableActionHandler;
use slackbot\handlers\action\UserInputActionHandler;
use slackbot\handlers\command\RestartCommandHandler;
use slackbot\handlers\command\StatusCommandHandler;
use slackbot\logging\handlers\ConsoleOutputHandler;
use slackbot\logging\handlers\SlackHandler;
use slackbot\logging\Logger;
use slackbot\logging\NamesResolver;
use slackbot\models\ArgvParser;
use slackbot\models\ConditionResolver;
use slackbot\models\Config;
use slackbot\models\HandlerExecutionResolver;
use slackbot\models\Registry;
use slackbot\models\Variables;
use slackbot\models\VariablesPlacer;
use slackbot\util\CurlRequest;
use slackbot\util\FileLoader;
use slackbot\util\PostParser;
use Symfony\Component\Yaml\Parser;
use slackbot\models\SlackApi;
use slackbot\models\SlackFacade;
use Cron\CronExpression;

class CoreBuilder
{
    public function buildContainer(ArgvParser $argvParser, Config $config = null)
    {
        $container = new Container();

        $container['started'] = time();

        $container['config'] = function () use ($config, $container) {
            return (null !== $config)
                ? $config
                : new Config($container['yaml_parser'], $container['file_loader']);
        };
        $container['variables_placer'] = function () {
            return new VariablesPlacer();
        };
        $container['cron_expression'] = function () {
            return CronExpression::factory('@daily');
        };
        $container['argv_parser'] = function () use ($argvParser) {
            return $argvParser;
        };
        $container['yaml_parser'] = function () {
            return new Parser();
        };
        $container['file_loader'] = function () {
            return new FileLoader();
        };
        $container['curl_request'] = function () {
            return new CurlRequest();
        };
        $container['post_parser'] = function () {
            return new PostParser();
        };
        $container['slack_api'] = function (Container $container) {
            $slackApi = new SlackApi(
                $container['curl_request']
            );
            $token = $container['config']->getEntry('auth.token');
            if ($token !== null) {
                $slackApi->setToken($token);
            }
            return $slackApi;
        };
        $container['slack_facade'] = function (Container $container) {
            return new SlackFacade(
                $container['slack_api']
            );
        };

        $container['names_resolver'] = function(Container $container) {
            return new NamesResolver($container['slack_facade']);
        };

        $container['condition_resolver'] = function () {
            return new ConditionResolver();
        };
        $container['handler_execution_resolver'] = function (Container $container) {
            return new HandlerExecutionResolver(
                $container['config'],
                $container['slack_api']
            );
        };
        $container['core_processor'] = function (Container $container) {
            return new CoreProcessor(
                $container['slack_facade'],
                $container['handler_execution_resolver']
            );
        };
        $container['output_manager'] = function (Container $container) {
            return new OutputManager(
                $container['slack_facade']
            );
        };

        $container['api_cache'] = function () {
            return new ApiCache(new MemoryStorage());
        };

        $container['action_send_message'] = function (Container $container) {
            return new SendMessageActionHandler($container['slack_facade'], $container['output_manager']);
        };
        $container['action_set_variable'] = function (Container $container) {
            return new SetVariableActionHandler($container['slack_facade']);
        };
        $container['action_if'] = function (Container $container) {
            $handler = new IfActionHandler(
                $container['slack_facade'],
                $container['condition_resolver']
            );
            $handler->setCoreProcessor($container['core_processor']);
            return $handler;
        };
        $container['action_loop'] = function (Container $container) {
            $handler = new LoopActionHandler(
                $container['slack_facade'],
                $container['condition_resolver']
            );
            $handler->setCoreProcessor($container['core_processor']);
            return $handler;
        };
        $container['action_user_input'] = function (Container $container) {
            return new UserInputActionHandler(
                $container['slack_facade'],
                $container['core_processor']
            );
        };
        $container['action_continue'] = function () {
            return new ContinueActionHandler();
        };
        $container['action_break'] = function () {
            return new BreakActionHandler();
        };
        $container['action_run_command'] = function (Container $container) {
            return new RunCommandActionHandler(
                $container['slack_facade']
            );
        };

        $container['core_processor']->addActionHandler($container['action_send_message']);
        $container['core_processor']->addActionHandler($container['action_set_variable']);
        $container['core_processor']->addActionHandler($container['action_if']);
        $container['core_processor']->addActionHandler($container['action_loop']);
        $container['core_processor']->addActionHandler($container['action_user_input']);
        $container['core_processor']->addActionHandler($container['action_continue']);
        $container['core_processor']->addActionHandler($container['action_break']);
        $container['core_processor']->addActionHandler($container['action_run_command']);

        if (class_exists('\slackbot\handlers\command\TestCommandHandler')) {
            $container['command_test'] = function () {
                return new \slackbot\handlers\command\TestCommandHandler();
            };
            $container['core_processor']->addCommandHandler($container['command_test']);
        }

        $container['command_restart'] = function () {
            return new RestartCommandHandler();
        };
        $container['core_processor']->addCommandHandler($container['command_restart']);

        $container['command_status'] = function () {
            return new StatusCommandHandler();
        };
        $container['core_processor']->addCommandHandler($container['command_status']);

        $container['server'] = $this->buildServer();

        return $container;
    }

//    public function buildCommandsCache()
//    {
//
//    }
//
    public function buildServer()
    {
        $server = new Server("SlackBot", "0.1");

        $server->post('/playbook/run/', function (Request $request, Response $response, $next) {
            $rawData = $request->getData();
            $postParser = Registry::get('container')['post_parser'];
            $parsedData = $postParser->parse($rawData);

            $playbook = urldecode($parsedData['playbook']);
            $yamlParser = Registry::get('container')['yaml_parser'];
            $playbook = $yamlParser->parse($playbook);

            /** @var SlackApi $slackApi */
            $slackApi = Registry::get('container')['slack_api'];
            $playbookToken = Ar::get($playbook, 'auth.token');

            $oldToken = null;
            if (null !== $playbookToken) {
                $oldToken = $slackApi->getToken();
                $slackApi->setToken($playbookToken);
            }

            $executor = new PlaybookExecutor(Registry::get('container')['core_processor']);
            Variables::clear();
            $executor->execute($playbook);
            $response->write('Playbook executed successfully');
            $response->end();

            if (null !== $playbookToken) {
                $slackApi->setToken($oldToken);
            }

            $fileName = basename($parsedData['filename']);
            echo '[INFO] Executing playbook file ' . $fileName . "\n";
            $next();
        });

        $server->post('/command/run/', function (Request $request, Response $response, $next) {
            $rawData = $request->getData();
            $postParser = Registry::get('container')['post_parser'];
            $parsedData = $postParser->parse($rawData);

            $command = urldecode($parsedData['command']);

            $coreProcessor = Registry::get('container')['core_processor'];
            $dto = new RequestDto();
            $dto->setData([
                'type' => 'message',
                'channel' => 'cron',
                'user' => 'cron',
                'text' => $command,
                'ts' => time()
            ]);
            $coreProcessor->processCommand($dto);

            $response->write('Playbook executed successfully');
            $response->end();

            echo '[INFO] Executing command ' . $command . "\n";
            $next();
        });

        $server->post('/process/message/', function (Request $request, Response $response, $next) {
            $response->sendHeaders();
            //$response->writeJson(['ok' => true]);
            $response->write('ok');
            $response->end();

            $rawData = $request->getData();
            $postParser = Registry::get('container')['post_parser'];
            $parsedData = $postParser->parse($rawData);

            /** @var CoreProcessor $coreProcessor */
            $coreProcessor = Registry::get('container')['core_processor'];
            $dto = new RequestDto();
            $dto->setSource('rtm');
            $dto->setData(json_decode(Ar::get($parsedData, 'message'), true));
            $coreProcessor->process($dto);

            $next();
        });

        $server->get('/info/cron/', function (Request $request, Response $response, $next) {
            $response->writeJson(Registry::get('container')['config']->getSection('cron'));
            $response->end();

            $next();
        });

        return $server;
    }

    public function buildLogger(Container $container)
    {
        $logger = new Logger($container['names_resolver']);

        /** @var Config $config */
        $config = $container['config'];
        $loggingConfig = $config->getSection('logging');

        $shouldResolve = Ar::get($loggingConfig, 'resolve') ?: false;
        $logger->setResolveNames($shouldResolve);

        $loggingEntries = Ar::get($loggingConfig, 'handlers') ?: [];
        Ar::each($loggingEntries, function($loggingEntry) use ($logger, $container) {
            $filter = Ar::get($loggingEntry, 'filter') ?: 255;

            $handlerName = Ar::get($loggingEntry, 'handler');

            switch ($handlerName) {
                case 'slack':
                    $channels = Ar::get($loggingEntry, 'channels') ?: [];
                    $handler = (new SlackHandler($container['slack_facade']))
                        ->setChannels($channels)
                        ->setFilter($filter);
                    break;

                case 'console':
                    $handler = (new ConsoleOutputHandler())
                        ->setFilter($filter);
                    break;

                default:
                    return;
            }

            $logger->addHandler($handler);
        });

        $container['logger'] = $logger;
    }
}
