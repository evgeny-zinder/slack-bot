<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\models\ConditionResolver;
use slackbot\Util;

/**
 * Class IfActionHandler
 * @package slackbot\handlers\action
 */
class IfActionHandler extends BaseActionHandler
{
    /** @var ConditionResolver */
    private $conditionResolver;

    /**
     * IfActionHandler constructor.
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
        return 'if' === $dto->getAction();
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        if ($this->conditionResolver->isConditionMet(
            $dto->get('condition'),
            Variables::all()
        )) {
            $this->processActions($dto->get('then'));
        } else {
            $else = $dto->get('else');
            if (is_array($else) && count($else) > 0) {
                $this->processActions($else);
            }
        }

    }
}
