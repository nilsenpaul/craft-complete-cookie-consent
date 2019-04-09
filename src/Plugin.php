<?php

namespace nilsenpaul\cookieconsent;

use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\models\CookieSettings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;
use nilsenpaul\cookieconsent\variables\CompleteCookieConsentVariable;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use yii\helpers\Html;
use yii\helpers\Markdown;

class Plugin extends \craft\base\Plugin
{
    public static $instance;

    public $hasCpSettings = true;
    public $hasCpSection = true;

    public $localIps = ['127.0.0.1', '::1'];

    public function init()
    {
        parent::init();

        // Set instance
        self::$instance = $this;

        // Load settings
        $settings = $this->getSettings();

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

        // Register variable
        Event::on(
            CraftVariable::class, 
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('ccc', CompleteCookieConsentVariable::class);
            }
        );

        // Trigger the asset bundles, if need be
        $request = Craft::$app->getRequest();
        if (
            $this->isInstalled 
            && !$request->isConsoleRequest
            && !$request->isCpRequest
            && $settings->pluginIsActive
        ) {
            $this->registerAssetBundles();

            // If Implied Consent is allowed, set a cookie to see if the visitor has been here before
            if ($settings->consentType === 'implied' && !$request->isActionRequest) {
                $this->cookies->countVisit();
            }
        }
    }

    protected function bannerShouldBeShown()
    {
        $settings = $this->getSettings();
        $devMode = Craft::$app->getConfig()->general->devMode;
        $ip = Craft::$app->getRequest()->remoteIp;

        // Implied Consent, and not first page load?
        if ($settings->consentType === 'implied' && !$this->cookies->isFirstVisit()) {
            return false;
        }

        // Local IP or devMode? Always show the banner
        if (\in_array($ip, $this->localIps) || $devMode) {
            return true;
        }

        // With the Geo API turned off, there's no way to determine if the banner should be visible. Always show it.
        if (!$settings->useIpApi) {
            return true;
        }

        return $this->geo->isEuropeanCountry();
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
