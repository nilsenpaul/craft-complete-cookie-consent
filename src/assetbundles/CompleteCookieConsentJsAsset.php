<?php

namespace nilsenpaul\cookieconsent\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CompleteCookieConsentJsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@nilsenpaul/cookieconsent/resources';
        $this->js = [
            'ccc.js',
        ];

        parent::init();
    }
}
