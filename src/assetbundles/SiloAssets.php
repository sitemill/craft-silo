<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SiloAssets extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        $this->sourcePath = '@sitemill/silo/assetbundles/dist';
        $this->css = [
            ['silo.scss', 'position' => \yii\web\View::POS_END]
        ];
        $this->js = [
            'silo.js'
//            'manifest.js',
//            'vendor.js'
        ];
        parent::init();
    }
}
