<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\controllers;

use craft\helpers\FileHelper;

use Craft;
use craft\web\Controller;
use sitemill\silo\Silo;
use sitemill\silo\elements\SiloAsset;
use sitemill\silo\services\SiloAssets;
use yii\base\Exception;

/**
 * @author    SiteMill
 * @package   Silo
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

        $request = Craft::$app->getRequest();

        // Get the files
        $fileIds = $request->getRequiredParam('files');

        // Decide whether or not to archive em'
        if (count($fileIds) > 1) {
            $file = Silo::$plugin->download->archive($fileIds);
        } else {
            $file = SiloAsset::find()->id($fileIds[0])->one()->file->getCopyOfFile();
        }

        // Push the download
        if (!$response = Craft::$app->getResponse()->sendFile($file, null, ['forceDownload' => true])) {
            throw new Exception(Craft::t('library', 'Failed to download files'));
        }

//
        foreach ($fileIds as $siloAssetId) {
            SiloAssets::instance()->incrementDownloads($siloAssetId);
        }


        // Delete the temp file
        FileHelper::unlink($file);

        return $response;

    }
}
