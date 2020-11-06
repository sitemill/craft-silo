<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\fields;

use craft\fields\BaseRelationField;
use sitemill\dam\elements\DamAsset;

class DamAssetsField extends BaseRelationField
{
    public static function displayName(): string
    {
        return \Craft::t('dam', 'DAM Assets');
    }

    protected static function elementType(): string
    {
        return DamAsset::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('dam', 'Select an asset');
    }
}