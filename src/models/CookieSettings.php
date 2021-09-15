<?php

namespace nilsenpaul\cookieconsent\models;

use nilsenpaul\cookieconsent\Plugin;

use Craft;
use craft\base\Model;

use yii\helpers\ArrayHelper;

class CookieSettings extends Model
{
    public $consentNeeded = true;
    public $consentSubmitted = false;
    public $consentImplied = false;
    public $consent = false;
    public $cookieTypes = null;

    public function init()
    {
        $settings = Plugin::$instance->getSettings(null, true);

        $consent = [];

        // Set default cookie type values
        if ($settings->consentType == 'implied') {
            foreach ($settings->cookieTypes as $cookieType) {
                if ($cookieType['defaultOn']) {
                    $consent[$cookieType['handle']] = true;
                } else {
                    $consent[$cookieType['handle']] = false;
                }
            }

            $this->consentImplied = true;
        } else {
            foreach ($settings->cookieTypes as $cookieType) {
                $consent[$cookieType['handle']] = false;
            }
        }

        // Include cookie type settings in info array
        $this->cookieTypes = ArrayHelper::map($settings['cookieTypes'], 'handle', function ($cookieType) {
            return [
                'name' => $cookieType['name'],
                'description' => $cookieType['description'] ?? '',
                'defaultOn' => (Bool)$cookieType['defaultOn'],
                'required' => (Bool)$cookieType['required'],
            ];
        });

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
        $this->consentImplied = false;
    }
}
