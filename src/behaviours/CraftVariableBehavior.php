<?php
namespace sitemill\silo\behaviours;

use Craft;
use sitemill\silo\elements\db\SiloAssetQuery;
use sitemill\silo\elements\SiloAsset;
use yii\base\Behavior;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class CraftVariableBehavior extends Behavior
{
    public function siloAssets($criteria = null): SiloAssetQuery
    {
        $query = SiloAsset::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}