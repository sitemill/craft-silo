<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class DamAssets extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        $this->sourcePath = '@sitemill/dam/assetbundles/dist';
        $this->css = [
            ['app.scss', 'position' => \yii\web\View::POS_END]
        ];
        $this->js = [
//            'manifest.js',
//            'vendor.js',
            'dam.js'
        ];
        parent::init();
    }
}
