<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\models\ConditionResolver;
use slackbot\Util;

class LoopActionHandler extends BaseActionHandler
{
    public function __construct(SlackFacade $slackFacade, ConditionResolver $conditionResolver)
    {
        parent::__construct($slackFacade);
        $this->conditionResolver = $conditionResolver;
    }

    /**
     * @param ActionDto $dto
     * @return boolean
     */
    public function canProcessAction(ActionDto $dto)
    {
        return Util::arrayGet($dto->getData(), 'action') === 'loop';
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        if (Util::arrayGet($dto->getData(), 'type') === 'while') {
            $this->processWhileLoop($dto);
        } elseif (Util::arrayGet($dto->getData(), 'type') === 'until') {
            $this->processUntilLoop($dto);
        } else {
            throw new \LogicException('Bad loop type');
        }
    }

    private function processWhileLoop(ActionDto $dto) {
        $actions = Util::arrayGet($dto->getData(), 'actions');
        while ($this->conditionResolver->isConditionMet(
            Util::arrayGet($dto->getData(), 'condition'),
            Variables::all()
        )) {
            if (Variables::get('flowcontrol.continue') === true) {
                Variables::remove('flowcontrol.continue');
                continue;
            }
            if (Variables::get('flowcontrol.break') === true) {
                Variables::remove('flowcontrol.break');
                break;
            }
            $this->processActions($actions);
        }
    }

    private function processUntilLoop(ActionDto $dto) {
        $actions = Util::arrayGet($dto->getData(), 'actions');
        while (!$this->conditionResolver->isConditionMet(
            Util::arrayGet($dto->getData(), 'condition'),
            Variables::all()
        )) {
            if (Variables::get('flowcontrol.continue') === true) {
                Variables::remove('flowcontrol.continue');
                continue;
            }
            if (Variables::get('flowcontrol.break') === true) {
                Variables::remove('flowcontrol.break');
                break;
            }

            $this->processActions($actions);
        }

    }
}
