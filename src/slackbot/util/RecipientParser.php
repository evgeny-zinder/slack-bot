<?php

namespace slackbot\util;

class RecipientParser
{
    public static function parse($recipients)
    {
        return preg_split('/\s*,\s*/', $recipients);
    }
}
