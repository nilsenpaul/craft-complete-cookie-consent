<?php

namespace nilsenpaul\cookieconsent\controllers;

use nilsenpaul\cookieconsent\Plugin;
use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;

use Craft;
use craft\web\Controller;

class ConsentController extends Controller
{
    public $allowAnonymous = ['submit', 'banner-info'];

    public function actionSubmit()
    {
        $this->requirePostRequest();

        $settings = Plugin::$instance->getSettings();
        $request = Craft::$app->getRequest();
        $checkedCookieTypes = $request->post()['cookieTypes'];

        // Save new cookie settings to a cookie
        $cookieTypesWithConsent = [];
        foreach ($settings->cookieTypes as $cookieType) {
            if ($cookieType['required'] || \in_array($cookieType['handle'], $checkedCookieTypes)) {
                $cookieTypesWithConsent[] = $cookieType['handle'];
            }
        }

        Plugin::$instance->cookies->setConsentCookie($cookieTypesWithConsent);
        Craft::$app->getSession()->setNotice(Craft::t('complete-cookie-consent', 'Cookie preferences have been saved'));

        return $this->redirectToPostedUrl();
    }

    public function actionBannerInfo()
    {
        return $this->asJson([
            'consentInfo' => Plugin::$instance->consent->getInfo(),
            'pluginSettings' => Plugin::$instance->getSettings(),
            'isFirstVisit' => Plugin::$instance->cookies->isFirstVisit(),
            'csrfTokenName' => Craft::$app->getConfig()->general->csrfTokenName,
            'csrfTokenValue' => Craft::$app->getRequest()->csrfToken,
            'bannerShouldBeShown' => $this->bannerShouldBeShown(),
        ]);
    }

    protected function bannerShouldBeShown()
    {
        $settings = Plugin::$instance->getSettings();
        $devMode = Craft::$app->getConfig()->general->devMode;
        $ip = Craft::$app->getRequest()->remoteIp;

        // Local IP or devMode? Always show the banner
        if (\in_array($ip, Plugin::$instance->localIps) || $devMode) {
            return true;
        }

        // Only admin and not a logged in admin?
        if ($settings->onlyShowAdmins && !Craft::$app->user->isAdmin) {
            return false;
        }

        // With the Geo API turned off, there's no way to determine if the banner should be visible. Always show it.
        if (!$settings->useIpApi) {
            return true;
        }

        return Plugin::$instance->geo->isEuropeanCountry();
    }
}

