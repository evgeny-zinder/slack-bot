<?php

namespace slackbot\handlers\action;

use slackbot\CoreProcessor;
use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;

/**
 * Class BaseActionHandler
 * @package slackbot\handlers\action
 */
abstract class BaseActionHandler implements ActionHandlerInterface
{
    /** @var SlackFacade */
    protected $slackFacade;

    /** @var CoreProcessor */
    protected $coreProcessor;

    /**
     * BaseActionHandler constructor.
     * @param SlackFacade $slackFacade
     */
    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    /**
     * @param CoreProcessor $coreProcessor
     */
    public function setCoreProcessor(CoreProcessor $coreProcessor)
    {
        $this->coreProcessor = $coreProcessor;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return __CLASS__;
    }

    /**
     * @param ActionDto $dto
     * @return bool
     */
    abstract public function canProcessAction(ActionDto $dto);

    /**
     * @param ActionDto $dto
     * @return null
     */
    abstract public function processAction(ActionDto $dto);

    /**
     * @return ActionDto
     */
    protected function createActionDto()
    {
        return new ActionDto();
    }

    /**
     * @param ActionDto $dto
     * @param array $data
     */
    protected function populateActionDto(ActionDto $dto, $data)
    {
        $dto->setData($data);
    }

    /**
     * @param array $actions
     */
    protected function processActions($actions)
    {
        foreach ($actions as $action) {
            if (true === Variables::get('flowcontrol.continue')) {
                break;
            }
            if (true === Variables::get('flowcontrol.break')) {
                break;
            }

            $actionDto = $this->createActionDto();
            $this->populateActionDto($actionDto, $action);
            $this->coreProcessor->processAction($actionDto);
        }
    }
}
