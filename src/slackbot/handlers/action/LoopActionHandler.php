<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\models\ConditionResolver;

/**
 * Class LoopActionHandler
 * @package slackbot\handlers\action
 */
class LoopActionHandler extends BaseActionHandler
{
    /** @var ConditionResolver */
    private $conditionResolver;

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
        return 'loop' === $dto->getAction();
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        if ('while' === $dto->get('type')) {
            $this->processWhileLoop($dto);
        } elseif ('until' === $dto->get('type')) {
            $this->processUntilLoop($dto);
        } else {
            throw new \LogicException('Bad loop type');
        }
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    private function processWhileLoop(ActionDto $dto)
    {
        $actions = $dto->get('actions');
        while ($this->conditionResolver->isConditionMet(
            $dto->get('condition'),
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
    private function processUntilLoop(ActionDto $dto)
    {
        $actions = $dto->get('actions');
        while (!$this->conditionResolver->isConditionMet(
            $dto->get('condition'),
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
