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
    public function getDamAssetById(int $damAssetId, $siteId = null, array $criteria = []) {

        if (!$damAssetId) {
            return null;
        }

        return Craft::$app->getElements()->getElementById($damAssetId, DamAsset::class, $siteId, $criteria);
    }

    /*
    * @return mixed
    */
    public function archiveLibraryAsset(DamAsset $damAsset): bool
    {
        $damAsset->isArchived = 1;
        return Craft::$app->elements->saveElement($damAsset);
    }

    /*
    * @return mixed
    */
    public function incrementDownloads(int $damAssetId): bool
    {
        $damAsset = $this->getDamAssetById($damAssetId);
        $damAsset->downloads = ++$damAsset->downloads;
        return Craft::$app->elements->saveElement($damAsset);
    }

}
