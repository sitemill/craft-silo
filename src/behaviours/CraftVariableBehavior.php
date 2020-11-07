<?php
namespace sitemill\dam\behaviours;

use Craft;
use sitemill\dam\elements\db\DamAssetQuery;
use sitemill\dam\elements\DamAsset;
use yii\base\Behavior;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class CraftVariableBehavior extends Behavior
{
    public function damAssets($criteria = null): DamAssetQuery
    {
        $query = DamAsset::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}