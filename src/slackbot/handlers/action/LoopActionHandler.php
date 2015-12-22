<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\models\ConditionResolver;
use slackbot\Util;

/**
 * Class LoopActionHandler
 * @package slackbot\handlers\action
 */
class LoopActionHandler extends BaseActionHandler
{
    /**
     * LoopActionHandler constructor.
     * @param SlackFacade $slackFacade
     * @param ConditionResolver $conditionResolver
     */
    public function __construct(SlackFacade $slackFacade, ConditionResolver $conditionResolver)
    {
        parent::__construct($slackFacade);
        $this->conditionResolver = $conditionResolver;
    }

    /**
     * @param ActionDto $dto
     * @return bool
     */
    public function canProcessAction(ActionDto $dto)
    {
        return 'loop' === Util::arrayGet($dto->getData(), 'action');
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        if ('while' === Util::arrayGet($dto->getData(), 'type')) {
            $this->processWhileLoop($dto);
        } elseif ('until' === Util::arrayGet($dto->getData(), 'type')) {
            $this->processUntilLoop($dto);
        } else {
            throw new \LogicException('Bad loop type');
        }
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    private function processWhileLoop(ActionDto $dto) {
        $actions = Util::arrayGet($dto->getData(), 'actions');
        while ($this->conditionResolver->isConditionMet(
            Util::arrayGet($dto->getData(), 'condition'),
            Variables::all()
        )) {
            if (true === Variables::get('flowcontrol.continue')) {
                Variables::remove('flowcontrol.continue');
                continue;
            }
            if (true === Variables::get('flowcontrol.break')) {
                Variables::remove('flowcontrol.break');
                break;
            }

            $this->processActions($actions);
        }
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    private function processUntilLoop(ActionDto $dto) {
        $actions = Util::arrayGet($dto->getData(), 'actions');
        while (!$this->conditionResolver->isConditionMet(
            Util::arrayGet($dto->getData(), 'condition'),
            Variables::all()
        )) {
            if (true === Variables::get('flowcontrol.continue')) {
                Variables::remove('flowcontrol.continue');
                continue;
            }
            if (true === Variables::get('flowcontrol.break')) {
                Variables::remove('flowcontrol.break');
                break;
            }

            $this->processActions($actions);
        }
    }
}
