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
class SiloSettings extends Component
{
    // Public Methods
    // =========================================================================


    /*
    * @return mixed
    */
    public function saveFieldLayout($fieldLayout)
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
        $path = "silo";
        Craft::$app->projectConfig->set($path, $myComponentConfig);
    }

    /*
    * @return mixed
    */
    public function handleUpdateFieldLayout(ConfigEvent $event)
    {
        $data = $event->newValue;
        $fieldLayout = Craft::$app->fields->getLayoutByType(SiloAsset::class);
        $layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
        $layout->id = $fieldLayout->id;
        $layout->type = SiloAsset::class;
        $layout->uid = key($data['fieldLayouts']);
        Craft::$app->fields->saveLayout($layout);
    }

}
