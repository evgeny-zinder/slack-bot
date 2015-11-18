<?php

namespace slackbot\handlers\action;

use slackbot\dto\ActionDto;
use slackbot\models\SlackFacade;
use slackbot\models\Variables;
use slackbot\models\ConditionResolver;
use slackbot\Util;

class IfActionHandler extends BaseActionHandler
{
    /** @var ConditionResolver */
    private $conditionResolver;

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
        return Util::arrayGet($dto->getData(), 'action') === 'if';
    }

    /**
     * @param ActionDto $dto
     * @return null
     */
    public function processAction(ActionDto $dto)
    {
        if ($this->conditionResolver->isConditionMet(
            Util::arrayGet($dto->getData(), 'condition'),
            Variables::all()
        )) {
            $this->processActions(Util::arrayGet($dto->getData(), 'then'));
        } else {
            $else = Util::arrayGet($dto->getData(), 'else');
            if (is_array($else) && count($else) > 0) {
                $this->processActions(Util::arrayGet($dto->getData(), 'else'));
            }
        }

    }
}
