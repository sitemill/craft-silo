<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\services;

use sitemill\silo\elements\SiloAsset;

use Craft;
use craft\base\Component;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SiloAssets extends Component
{
    // Public Methods
    // =========================================================================


    /*
     * @return mixed
     */
    public function getSiloAssetById(int $siloAssetId, $siteId = null, array $criteria = [])
    {

        if (!$siloAssetId) {
            return null;
        }

        return Craft::$app->getElements()->getElementById($siloAssetId, SiloAsset::class, $siteId, $criteria);
    }

    /*
    * @return mixed
    */
    public function archiveLibraryAsset(SiloAsset $siloAsset): bool
    {
        $siloAsset->isArchived = 1;
        return Craft::$app->elements->saveElement($siloAsset);
    }

    /*
    * @return mixed
    */
    public function incrementDownloads(int $siloAssetId): bool
    {
        $siloAsset = $this->getSiloAssetById($siloAssetId);
        $siloAsset->downloads = ++$siloAsset->downloads;
        return Craft::$app->elements->saveElement($siloAsset);
    }

}
