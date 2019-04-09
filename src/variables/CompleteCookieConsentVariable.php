<?php

namespace nilsenpaul\cookieconsent\variables;

use nilsenpaul\cookieconsent\Plugin;

use Craft;

class CompleteCookieConsentVariable
{
    public function getConsentInfo()
    {
        return Plugin::$instance->consent->getInfo();
    }
}
