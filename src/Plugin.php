<?php

namespace nilsenpaul\cookieconsent;

use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\View;

use yii\base\Event;
use yii\helpers\Html;

class Plugin extends \craft\base\Plugin
{
    public static $instance;

    public $hasCpSettings = true;
    public $hasCpSection = true;

    public function init()
    {
        parent::init();

        // Set instance
        self::$instance = $this;

        // CP Routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge(
                    $event->rules,
                    [
                        'complete-cookie-consent' => 'complete-cookie-consent/settings',
                        'complete-cookie-consent/<siteHandle:{handle}>' => 'complete-cookie-consent/settings',
                    ]
                );
            }
        );

        // Show banner
        Event::on(
            View::class,
            View::EVENT_END_BODY,
            function (Event $event) {
                echo Html::tag('div', Html::tag('cookiebanner'), ['id' => 'ccc']);
            }
        );


        // Trigger the asset bundles, if need be
        $request = Craft::$app->getRequest();
        if ($this->isInstalled && !$request->isConsoleRequest && !$request->isCpRequest) {
            $this->registerAssetBundles();
        }
    }

    protected function registerAssetBundles()
    {
        $settings = $this->getSettings();

        // Include CSS, but only if we need to
        if ($settings->includeCss) {
            $view = Craft::$app->getView();
            $view->registerAssetBundle('nilsenpaul\\cookieconsent\\assetbundles\\CompleteCookieConsentCssAsset');
        }  

        // Include JS
        $view->registerAssetBundle('nilsenpaul\\cookieconsent\\assetbundles\\CompleteCookieConsentJsAsset');
        $view->registerJs('
            window.csrfTokenName = "' . Craft::$app->getConfig()->general->csrfTokenName . '";
            window.csrfTokenValue = "' . Craft::$app->getRequest()->csrfToken . '";
            var cccSettings = ' . json_encode($settings)
        , View::POS_BEGIN);
    }

    public function getSettings($siteId = null)
    {
        $settingsModel = parent::getSettings();

        // Get settings record for current site
        $settingsRecord = SettingsRecord::find()
            ->where([
                'siteId' => $siteId === null ? Craft::$app->getSites()->currentSite->id : $siteId,
            ])
            ->one();

        if ($settingsRecord) {
            $settings = json_decode($settingsRecord->settings, true);
            $settingsModel->load($settings, '');
        }

        return $settingsModel;
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    public function getSettingsResponse()
    {
        // Redirect to the settings page
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('complete-cookie-consent'));
    }
}
