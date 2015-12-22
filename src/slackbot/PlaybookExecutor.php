<?php

namespace slackbot;

use slackbot\dto\ActionDto;

/**
 * Class PlaybookExecutor
 * Executes already parsed playbook
 * @package slackbot
 */
class PlaybookExecutor
{
    /** @var CoreProcessor */
    private $coreProcessor;

    /**
     * PlaybookExecutor constructor.
     * @param CoreProcessor $coreProcessor
     */
    public function __construct(CoreProcessor $coreProcessor)
    {
        $this->coreProcessor = $coreProcessor;
    }

    /**
     * @param $playbook
     * @return bool
     */
    public function execute($playbook)
    {
        foreach ($playbook['actions'] as $action) {
            $actionDto = $this->createDto();
            $this->populateDto($actionDto, $action);
            $this->coreProcessor->processAction($actionDto);
        }

        return true;
    }

    /**
     * @return ActionDto
     */
    private function createDto()
    {
        return new ActionDto();
    }

    /**
     * @param ActionDto $dto
     * @param $data
     */
    private function populateDto(ActionDto $dto, $data)
    {
        $dto->setData($data);
    }
}
