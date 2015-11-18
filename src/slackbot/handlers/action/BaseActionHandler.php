<?php

namespace slackbot\handlers\action;

use slackbot\CoreProcessor;
use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;

abstract class BaseActionHandler implements ActionHandlerInterface
{
    /** @var SlackFacade */
    protected $slackFacade;

    /** @var CoreProcessor */
    protected $coreProcessor;

    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    public function setCoreProcessor(CoreProcessor $coreProcessor)
    {
        $this->coreProcessor = $coreProcessor;
    }

    public function getId()
    {
        return __CLASS__;
    }

    /**
     * @param ActionDto $dto
     * @return boolean
     */
    abstract public function canProcessAction(ActionDto $dto);

    /**
     * @param ActionDto $dto
     * @return null
     */
    abstract public function processAction(ActionDto $dto);

    protected function createActionDto()
    {
        return new ActionDto();
    }

    protected function populateActionDto(ActionDto $dto, $data)
    {
        $dto->setData($data);
    }

    protected function processActions($actions) {
        foreach ($actions as $action) {
            if (Variables::get('flowcontrol.continue') === true) {
                break;
            }
            if (Variables::get('flowcontrol.break') === true) {
                break;
            }

            $actionDto = $this->createActionDto();
            $this->populateActionDto($actionDto, $action);
            $this->coreProcessor->processAction($actionDto);
        }
    }
}
