<?php

namespace slackbot;

use slackbot\dto\ActionDto;

class PlaybookExecutor
{
    /** @var CoreProcessor */
    private $coreProcessor;

    public function __construct(CoreProcessor $coreProcessor)
    {
        $this->coreProcessor = $coreProcessor;
    }

    public function execute($playbook)
    {
        foreach ($playbook['actions'] as $action) {
            $actionDto = $this->createDto();
            $this->populateDto($actionDto, $action);
            $this->coreProcessor->processAction($actionDto);
        }

        return true;
    }

    private function createDto()
    {
        return new ActionDto();
    }

    private function populateDto(ActionDto $dto, $data)
    {
        $dto->setData($data);
    }
}
