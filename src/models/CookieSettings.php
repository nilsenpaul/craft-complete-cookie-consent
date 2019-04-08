<?php

namespace nilsenpaul\cookieconsent\models;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Model;

class CookieSettings extends Model
{
    public $consentSubmitted = false;
    public $consent = false;

    public function init()
    {
        $settings = Plugin::$instance->getSettings(null, true);

        $consent = [];
        foreach ($settings->cookieTypes as $cookieType) {
            $consent[$cookieType['handle']] = false;
        }

        $this->consent = $consent;
    }

    public function populateFromCookie($cookieValue)
    {
        $settings = Plugin::$instance->getSettings(null, true);

        $consent = [];
        foreach ($settings->cookieTypes as $cookieType) {
            $consent[$cookieType['handle']] = in_array($cookieType['handle'], $cookieValue);
        }

        $this->consent = $consent;
        $this->consentSubmitted = true;
    }
}
