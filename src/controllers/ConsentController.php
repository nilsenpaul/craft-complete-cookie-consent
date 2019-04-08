<?php

namespace nilsenpaul\cookieconsent\controllers;

use nilsenpaul\cookieconsent\Plugin;
use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;

use Craft;
use craft\web\Controller;

class ConsentController extends Controller
{
    public $allowAnonymous = ['submit'];

    public function actionSubmit()
    {
        $this->requirePostRequest();

        $settings = Plugin::$instance->getSettings();
        $request = Craft::$app->getRequest();
        $checkedCookieTypes = $request->post()['cookieTypes'];

        // Save new cookie settings to a cookie
        Plugin::$instance->cookies->set($checkedCookieTypes);

        Craft::$app->getSession()->setNotice(Craft::t('complete-cookie-consent', 'Cookie preferences have been saved'));

        return $this->redirectToPostedUrl();
    }
}

