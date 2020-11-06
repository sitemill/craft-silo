<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\services;

use sitemill\dam\elements\DamAsset;

use Craft;
use craft\base\Component;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class DamAssets extends Component
{
    // Public Methods
    // =========================================================================


    /*
     * @return mixed
     */
    public function getDamAssetById(int $libraryAssetId, $siteId = null, array $criteria = []) {

        if (!$libraryAssetId) {
            return null;
        }

        return Craft::$app->getElements()->getElementById($libraryAssetId, DamAsset::class, $siteId, $criteria);
    }

    /*
    * @return mixed
    */
    public function archiveLibraryAsset(DamAsset $libraryAsset): bool
    {
        $libraryAsset->isArchived = 1;
        return Craft::$app->elements->saveElement($libraryAsset);
    }

    /*
    * @return mixed
    */
    public function incrementDownloads(int $libraryAssetId): bool
    {
        $libraryAsset = $this->getLibraryAssetById($libraryAssetId);
        $libraryAsset->downloads = ++$libraryAsset->downloads;
        return Craft::$app->elements->saveElement($libraryAsset);
    }

}
