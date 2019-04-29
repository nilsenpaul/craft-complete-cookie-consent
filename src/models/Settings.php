<?php

namespace nilsenpaul\cookieconsent\models;

use Craft;

use craft\base\Model;
use craft\validators\ColorValidator;

class Settings extends Model
{
    public $pluginName = 'Complete Cookie Consent';
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
    public $primaryButtonText = 'Save settings';
    public $primaryButtonBackgroundColor = '#404040';
    public $primaryButtonTextColor = '#ffffff';
    public $showSecondaryButton = false;
    public $secondaryButtonText = 'Learn more';
    public $secondaryButtonBackgroundColor = '#efefef';
    public $secondaryButtonTextColor = '#404040';
    public $secondaryButtonLink;
    public $secondaryButtonLinkToEntry;
    public $secondaryButtonOpenInNewTab = false;
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
        'primaryButtonText',
        'primaryButtonBackgroundColor',
        'primaryButtonTextColor',
        'showSecondaryButton',
        'secondaryButtonText',
        'secondaryButtonBackgroundColor',
        'secondaryButtonTextColor',
        'secondaryButtonHref',
        'secondaryButtonOpenInNewTab',
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

        // Set new primary button attributes if settings existed
        $this->primaryButtonText = $this->bannerButtonText;
        $this->primaryButtonBackgroundColor = $this->bannerButtonColor;
        $this->primaryButtonTextColor = $this->bannerButtonTextColor;
    }

    public function rules()
    {
        return [
            [['bannerColor', 'primaryButtonBackgroundColor', 'primaryButtonTextColor'], ColorValidator::class],
            [['pluginName', 'primaryButtonText', 'primaryButtonBackgroundColor', 'primaryButtonTextColor', 'cookieTypes', 'cookieName'], 'required'],
            [['bannerPosition'], 'in', 'range' => ['top', 'left', 'bottom', 'right', 'center']],
            [['consentType'], 'in', 'range' => ['implied', 'explicit']],
            [['geolocationMethod'], 'in', 'range' => ['none', 'geoIpLite', 'ipApi']],
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
                'showSecondaryButton',
                'secondaryButtonText',
                'secondaryButtonBackgroundColor',
                'secondaryButtonTextColor',
                'secondaryButtonLink',
                'secondaryButtonLinkToEntry',
                'secondaryButtonOpenInNewTab',
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

    public function getSecondaryButtonEntry()
    {
        if (!empty($this->secondaryButtonLinkToEntry)) {
            return Craft::$app->getElements()->getElementById($this->secondaryButtonLinkToEntry[0]);
        }

        return null;
    }

    public function getSecondaryButtonHref()
    {
        $entry = $this->getSecondaryButtonEntry();

        if ($entry) {
            return $entry->url;
        }

        return $this->secondaryButtonLink;
    }
}
