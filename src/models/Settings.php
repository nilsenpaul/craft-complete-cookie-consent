<?php

namespace nilsenpaul\cookieconsent\models;

use Craft;

use craft\base\Model;
use craft\validators\ColorValidator;

class Settings extends Model
{
    public $pluginIsActive = false;
    public $consentType = 'explicit';
    public $includeCss = true;
    public $onlyShowAdmins = false;
    public $rememberFor = 86400;
    public $bannerColor = '#ffffff';
    public $bannerTitle = 'Hey! This banner needs your attention.';
    public $bannerText = 'European cookie laws require us to show you, the visitor, this message and give you a choice as to what cookies will be set.';
    public $bannerButtonText = 'Save settings';
    public $bannerButtonColor = '#404040';
    public $bannerButtonTextColor = '#ffffff';
    public $bannerPosition = 'bottom';
    public $showToggleAll = true;
    public $toggleAllText = 'Toggle all';
    public $cookieName = 'complete-cookie-consent';
    public $cookieTypes;
    public $useIpApi = false;
    public $ipApiKey;
    public $geolocationMethod = 'geoIpLite';

    protected $publicAttributes = [
        'consentType',
        'bannerColor',
        'bannerTitle',
        'bannerText',
        'bannerButtonText',
        'bannerButtonColor',
        'bannerButtonTextColor',
        'bannerPosition',
        'showToggleAll',
        'toggleAllText',
        'cookieTypes',
    ];

    public function init()
    {
        // Set default cookie types
        $this->cookieTypes = [
            [
                'handle' => 'necessary',
                'name' => Craft::t('complete-cookie-consent', 'Necessary'),
                'defaultOn' => true,
                'required' => true,
            ],
            [
                'handle' => 'preferences',
                'name' => Craft::t('complete-cookie-consent', 'Preferences'),
                'defaultOn' => true,
                'required' => false,
            ],
            [
                'handle' => 'statistics',
                'name' => Craft::t('complete-cookie-consent', 'Statistics'),
                'defaultOn' => false,
                'required' => false,
            ],
            [
                'handle' => 'marketing',
                'name' => Craft::t('complete-cookie-consent', 'Marketing'),
                'defaultOn' => false,
                'required' => false,
            ],
        ];

        // Set geolocationMethod to ipApi if it was previously selected
        if ($this->useIpApi) {
            $this->geolocationMethod = 'ipApi';
        }
    }

    public function rules()
    {
        return [
            [['bannerColor', 'bannerButtonColor', 'bannerButtonTextColor'], ColorValidator::class],
            [['bannerColor', 'bannerButtonColor', 'bannerButtonText', 'cookieTypes', 'cookieName'], 'required'],
            [['bannerPosition'], 'in', 'range' => ['top', 'left', 'bottom', 'right', 'center']],
            [['consentType'], 'in', 'range' => ['implied', 'explicit']],
            [['geolocationMethod'], 'in', 'range' => ['geoIpLite', 'ipApi']],
            [['rememberFor'], 'number'],
            [[
                'useIpApi',
                'ipApiKey',
                'pluginIsActive',
                'includeCss',
                'onlyShowAdmins',
                'showToggleAll',
                'toggleAllText',
                'bannerTitle',
                'bannerText',
                'bannerButtonText',
            ], 'safe'],
        ];
    }

    public function forFrontend()
    {
        $publicSettings = [];

        foreach ($this->publicAttributes as $attribute) {
            $publicSettings[$attribute] = $this->{$attribute};
        }

        return $publicSettings;
    }
}
