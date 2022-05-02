<?php

namespace nilsenpaul\cookieconsent\controllers;

use nilsenpaul\cookieconsent\Plugin;
use nilsenpaul\cookieconsent\models\Settings;
use nilsenpaul\cookieconsent\records\Settings as SettingsRecord;

use Craft;
use craft\web\Controller;

use yii\web\NotFoundHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
    public array|int|bool $allowAnonymous = ['update-ip-database'];

    public function actionIndex(string $siteHandle = null): Response
    {
        $variables = [];

        $siteId = $this->getSiteIdFromHandle($siteHandle);
        $templateTitle = Craft::t('complete-cookie-consent', 'Cookie consent settings');

        $variables['settings'] = Plugin::$instance->getSettings($siteId);

        // Set multisite variables
        $this->setMultiSiteVariables($siteHandle, $siteId, $variables);

        // Set tabs
        $variables['tabs'] = [
            [
                'label' => Craft::t('complete-cookie-consent', 'General'),
                'url' => '#general',
            ],
            [
                'label' => Craft::t('complete-cookie-consent', 'Appearance'),
                'url' => '#appearance',
            ],
            [
                'label' => Craft::t('complete-cookie-consent', 'Text'),
                'url' => '#text',
            ],
            [
                'label' => Craft::t('complete-cookie-consent', 'Buttons'),
                'url' => '#buttons',
            ],
            [
                'label' => Craft::t('complete-cookie-consent', 'Cookie settings'),
                'url' => '#cookies',
            ],
            [
                'label' => Craft::t('complete-cookie-consent', 'Geolocation'),
                'url' => '#geo',
            ],
        ];

        // Render the template
        return $this->renderTemplate('complete-cookie-consent/settings', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        
        $request = Craft::$app->getRequest();
        $siteId = (int)$request->getParam('siteId');

        // Get settings record for site ID, if any
        $settingsModel = $this->populateSettingsModel($request);

        if ($settingsModel->validate()) {
            $settingsRecord = SettingsRecord::find()
                ->where([
                    'siteId' => $siteId,
                ])
                ->one();

            if (!$settingsRecord) {
                $settingsRecord = new SettingsRecord();
                $settingsRecord->siteId = $siteId;
            }

            $settingsRecord->settings = json_encode($settingsModel);
            if ($settingsRecord->save()) {
                Craft::$app->getSession()->setNotice(Craft::t('complete-cookie-consent', 'Cookie consent preferences have been saved'));
            }

            return $this->redirectToPostedUrl();
        }
    }

    protected function populateSettingsModel($request)
    {
        $settings = new Settings();

        foreach ($settings->attributes as $key => $attribute) {
           $settings->{$key} = $request->getParam($key);
        }

        return $settings;
    }

    protected function setMultiSiteVariables($siteHandle, &$siteId, array &$variables, $element = null)
    {
        $sites = Craft::$app->getSites();

        if (Craft::$app->getIsMultiSite()) {
            $variables['enabledSiteIds'] = [];
            $variables['siteIds'] = [];

            foreach ($sites->getEditableSiteIds() as $editableSiteId) {
                $variables['enabledSiteIds'][] = $editableSiteId;
                $variables['siteIds'][] = $editableSiteId;
            }

            if (!\in_array($siteId, $variables['enabledSiteIds'], false)) {
                if (!empty($variables['enabledSiteIds'])) {
                    $siteId = reset($variables['enabledSiteIds']);
                } else {
                    $this->requirePermission('editSite:' . $siteId);
                }
            }
        }

        $variables['currentSiteId'] = empty($siteId) ? Craft::$app->getSites()->currentSite->id : $siteId;
        $variables['currentSiteHandle'] = empty($siteHandle) ? Craft::$app->getSites()->currentSite->handle : $siteHandle;

        // Should we show sites?
        $variables['showSites'] = Craft::$app->getIsMultiSite() && \count($variables['enabledSiteIds']);

        if ($variables['showSites']) {
            $variables['sitesMenuLabel'] = Craft::t('site', $sites->getSiteById((int)$variables['currentSiteId'])->name);
        } else {
            $variables['sitesMenuLabel'] = '';
        }
    }

    protected function getSiteIdFromHandle($siteHandle)
    {
        // Which site are we editing?
        if ($siteHandle !== null) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            
            if (!$site) {
                throw new NotFoundHttpException('This site handle is not valid: ' . $siteHandle);
            }

            $siteId = $site->id;
        } else {
            $siteId = Craft::$app->getSites()->currentSite->id;
        }

        return $siteId;
    }

    public function actionUpdateIpDatabase()
    {
        Plugin::$instance->geo->updateGeoIpLiteDatabase();
        exit;
    }
}
