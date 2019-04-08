<?php

namespace nilsenpaul\cookieconsent;

use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\models\CookieSettings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\View;

use yii\base\Event;
use yii\helpers\Html;
use yii\helpers\Markdown;

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
        $settings = $this->getSettings();
        if (
            $this->isInstalled 
            && !$request->isConsoleRequest
            && !$request->isCpRequest
            && $settings->pluginIsActive
            && $this->showBasedOnGeo()
            && (!$settings->onlyShowAdmins || Craft::$app->user->isAdmin)) {
            $this->setCookieVariable();
            $this->registerAssetBundles();
        }
    }

    protected function showBasedOnGeo()
    {
        $settings = $this->getSettings();

        if (!$settings->useIpApi) {
            return true;
        }

        $geoData = $this->geo->getInfo(true);

        if (!empty($geoData)) {
            if ($geoData['location']['is_eu']) {
                return true;
            }

            return false;
        }

        // Return true if no location was found, show the banner just to be sure
        return true;
    }

    protected function setCookieVariable()
    {
        $cookieValue = $this->cookies->get();

        $cookieSettings = new CookieSettings();
        if ($cookieValue) {
            $cookieSettings->populateFromCookie($cookieValue);
        }

        $view = Craft::$app->getView();
        $view->registerJs('var ccc = ' . json_encode($cookieSettings), View::POS_BEGIN);
    }

    protected function registerAssetBundles()
    {
        $settings = $this->getSettings(null, true);

        // Include CSS, but only if we need to
        $view = Craft::$app->getView();
        if ($settings->includeCss) {
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

    public function getSettings($siteId = null, $parseMarkdown = false)
    {
        $settingsModel = parent::getSettings();

        // Get settings record for current site
        if ($this->isInstalled) {
            $settingsRecord = SettingsRecord::find()
                ->where([
                    'siteId' => $siteId === null ? Craft::$app->getSites()->currentSite->id : $siteId,
                ])
                ->one();

            if ($settingsRecord) {
                $settings = json_decode($settingsRecord->settings, true);
                $settingsModel->load($settings, '');
            }
        }

        // Markdown text fields
        if ($parseMarkdown) {
            $settingsModel->bannerTitle = Markdown::processParagraph((string)$settingsModel->bannerTitle);
            $settingsModel->bannerText = Markdown::processParagraph((string)$settingsModel->bannerText);
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
