<?php

namespace nilsenpaul\cookieconsent\models;

use Craft;

use craft\base\Model;
use craft\validators\ColorValidator;

class Settings extends Model
{
    public $includeCss = true;
    public $onlyShowAdmins = false;
    public $bannerColor = '#ffffffee';
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

    public function init()
    {
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
                'defaultOn' => true,
                'required' => false,
            ],
            [
                'handle' => 'marketing',
                'name' => Craft::t('complete-cookie-consent', 'Marketing'),
                'defaultOn' => true,
                'required' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['bannerColor', 'bannerButtonColor', 'bannerButtonTextColor'], ColorValidator::class],
            [['bannerColor', 'bannerButtonColor', 'bannerButtonText', 'cookieTypes', 'cookieName'], 'required'],
            [['bannerPosition'], 'in', 'range' => ['top', 'left', 'bottom', 'right', 'center']],
            [[
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

}
