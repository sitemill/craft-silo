<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\controllers;

use Craft;
use craft\web\Controller;
use sitemill\silo\Silo;
use sitemill\silo\elements\SiloAsset;
use sitemill\silo\services\SiloSettings;
use yii\base\Exception;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->renderTemplate('silo/settings',['settings' => Silo::$plugin->getSettings()]);
    }

    /**
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $response = $this->request->getBodyParams();

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = SiloAsset::class;

        Silo::$plugin->siloSettings->saveFieldLayout($fieldLayout);

//        if (!Craft::$app->fields->saveLayout($fieldLayout)) {
//            $this->setFailFlash(Craft::t('app', 'Couldn’t save field layout.'));
//            return null;
//        }

        $plugin = Craft::$app->getPlugins()->getPlugin('silo');

        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $response)) {
            $this->setFailFlash(Craft::t('app', 'Couldn’t save plugin settings.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('app', 'Plugin settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
