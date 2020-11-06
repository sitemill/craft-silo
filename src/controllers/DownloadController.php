<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\controllers;

use craft\helpers\FileHelper;

use Craft;
use craft\web\Controller;
use sitemill\dam\Dam;
use sitemill\dam\services\DamAssets;
use yii\base\Exception;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class DownloadController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $libraryAssetsEnabled = Dam::$plugin->getSettings()->assetsSource == 'libraryAssets';

        $request = Craft::$app->getRequest();

        // Get the files
        $fileIds = $request->getRequiredParam('files');

        // Decide whether or not to archive em'
        if (count($fileIds) > 1) {
            $file = Dam::$plugin->download->archive($fileIds);
        } else {
            // Handle library asset if enabled
            if ($libraryAssetsEnabled) {
                $file = \sitemill\dam\elements\DamAsset::find()->id($fileIds[0])->one()->file->getCopyOfFile();
            } else {
                $file = Craft::$app->assets->getAssetById($fileIds[0])->getCopyOfFile();
            }
        }

        // Push the download
        if (!$response = Craft::$app->getResponse()->sendFile($file, null, ['forceDownload' => true])) {
            throw new Exception(Craft::t('library', 'Failed to download files'));
        }

        // Record the downloads
        if ($libraryAssetsEnabled) {
            foreach ($fileIds as $libraryAssetId) {
                DamAssets::instance()->incrementDownloads($libraryAssetId);
            }
        }

        // Delete the temp file
        FileHelper::unlink($file);

        return $response;

    }
}
