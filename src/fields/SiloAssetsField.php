<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\fields;

use craft\fields\BaseRelationField;
use sitemill\silo\elements\SiloAsset;

class SiloAssetsField extends BaseRelationField
{
    public static function displayName(): string
    {
        return \Craft::t('silo', 'Silo Assets');
    }

    protected static function elementType(): string
    {
        return SiloAsset::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('silo', 'Select an asset');
    }
}