<?php

namespace nilsenpaul\cookieconsent\services;

use nilsenpaul\cookieconsent\Plugin;
use nilsenpaul\cookieconsent\models\CookieSettings;

use Craft;
use craft\base\Component;

class Consent extends Component
{
    public function getInfo()
    {
        $cookieValue = Plugin::$instance->cookies->getConsentCookie();

        $cookieSettings = new CookieSettings();
        if ($cookieValue) {
            $cookieSettings->populateFromCookie($cookieValue);
        }

        return $cookieSettings;
    }
}
