<?php

namespace slackbot\logging;


use eznio\ar\Ar;
use slackbot\models\SlackFacade;

class NamesResolver
{
    protected $slackFacade;

    public function __construct(SlackFacade $slackFacade)
    {
        $this->slackFacade = $slackFacade;
    }

    public function resolve($message)
    {
        preg_match_all('/[DGCU]{1}[0-9A-Z]{8}/', $message, $matches);
        if (!is_array(Ar::get($matches, 0)) || 0 === count($matches[0])) {
            return $message;
        }
        foreach ($matches[0] as $match) {
            $replace = null;
            switch ($match[0]) {
                case 'U': $replace = $this->slackFacade->getUserNameById($match); break;
                case 'C':
                    $replace = $this->slackFacade->getChannelById($match)['name'];
                    if (null !== $replace) {
                        $replace = '#' . $replace;
                    }
                    break;
                case 'G': $replace = $this->slackFacade->getGroupById($match)['name']; break;
                case 'D':
                    $replace = $this->slackFacade->getUserInfoByDmId($match)['name'];
                    if (null !== $replace) {
                        $replace = '@' . $replace;
                    }
                    break;
            }

            if (null !== $replace) {
                $message = str_replace($match, $replace, $message);
            }
        }
        return $message;
    }
}
