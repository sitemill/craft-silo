<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\services;

use craft\elements\Asset;
use craft\helpers\FileHelper;
use sitemill\dam\Library;

use Craft;
use craft\base\Component;
use yii\base\Exception;
use ZipArchive;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class Download extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     * Thanks Oli
     */
    public function archive(array $assetIds, string $filename = 'Archive')
    {
        $libraryAssetsEnabled = Library::$plugin->getSettings()->assetsSource == 'libraryAssets';

        // Fetch the assets
        if ($libraryAssetsEnabled) {
            $assets = \sitemill\dam\elements\DamAsset::find()
                ->id($assetIds)
                ->limit(null)
                ->all();
        } else {
            $assets = Asset::find()
                ->id($assetIds)
                ->limit(null)
                ->all();
        }

        // Set the archive name to create (name chosen + stamp)
        $tempFile = Craft::$app->getPath()
                ->getTempPath() . DIRECTORY_SEPARATOR . $filename . '_' . time() . '.zip';

        // Create the archive
        $zip = new ZipArchive();

        // Open and fill
        if ($zip->open($tempFile, ZipArchive::CREATE) === true) {

            foreach ($assets as $asset) {

                // Grab the file
                if ($libraryAssetsEnabled) {
                    $assetObject = $asset->file;
                }

                // Get a temp copy of the file
                $file = $assetObject->getCopyOfFile();

                // Add to file to archive
                $zip->addFromString($assetObject->filename, $assetObject->getContents());

                // Delete the temp file
                FileHelper::unlink($file);
            }

            $zip->close();

            return $tempFile;
        }

        throw new Exception(Craft::t('library', 'Failed to generate the archive'));
    }
}
