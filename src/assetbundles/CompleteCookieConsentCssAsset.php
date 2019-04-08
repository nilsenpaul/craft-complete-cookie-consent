<?php

namespace nilsenpaul\cookieconsent\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CompleteCookieConsentCssAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@nilsenpaul/cookieconsent/resources';
        $this->css = [
            'ccc.css',
        ];

        parent::init();
    }
}
