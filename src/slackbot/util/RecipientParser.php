<?php

namespace slackbot\util;

/**
 * Class RecipientParser
 * @package slackbot\util
 */
class RecipientParser
{
    /**
     * @param $recipients
     * @return array
     */
    public static function parse($recipients)
    {
        return preg_split('/\s*,\s*/', $recipients);
    }
}
