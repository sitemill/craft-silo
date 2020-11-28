<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\services;

use craft\db\Table;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
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
    public function saveSiloAssets($fieldLayout)
    {
        $myComponentConfig = [];
        $fieldLayoutConfig = $fieldLayout->getConfig();
        if ($fieldLayoutConfig) {
            if (!$fieldLayout->id) {
                $layoutUid = $fieldLayout->uid = StringHelper::UUID();
            } else {
                $layoutUid = Db::uidById(Table::FIELDLAYOUTS, $fieldLayout->id);
            }
            $myComponentConfig['fieldLayouts'] = [
                $layoutUid => $fieldLayoutConfig
            ];
        }
        // Save it to the project config
        $path = "siloAssets";
        Craft::$app->projectConfig->set($path, $myComponentConfig);
    }

    /*
    * @return mixed
    */
    public function handleUpdateSiloAssets(ConfigEvent $event)
    {
        $data = $event->newValue;
        $fieldLayout = Craft::$app->fields->getLayoutByType(SiloAsset::class);
        $layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
        $layout->id = $fieldLayout->id;
        $layout->type = SiloAsset::class;
        $layout->uid = key($data['fieldLayouts']);
        Craft::$app->fields->saveLayout($layout);
    }

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
    public function archiveSiloAsset(SiloAsset $siloAsset): bool
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
