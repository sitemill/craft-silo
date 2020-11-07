<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\variables;

use craft\elements\Asset;
use craft\helpers\UrlHelper;
use sitemill\dam\elements\DamAsset;
use sitemill\dam\Library;

use Craft;
use Traversable;
use yii\db\Exception;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class DamVariable
{
    public function settings()
    {
        return Library::$plugin->getSettings();
    }

    public function userRegistrationEnabled()
    {
        return Craft::$app->getProjectConfig()->get('users')['allowPublicRegistration'];
    }

    public function download($assets)
    {
        $ids = [];

        // Detect if it's more than one asset
        if (is_array($assets)) {
            // If it's a DAM asset
            if($assets[0] instanceof DamAsset) {
                foreach ($assets as $asset) {
                    $ids[] = $asset->id;
                }
            } else {
                throw new Exception(Craft::t('dam', 'Download twig variable expects instances of DamAsset'));
            }
        } else {
            if($assets instanceof DamAsset) {
                $ids[] =  $assets->id;
            } else {
                throw new Exception(Craft::t('dam', 'Download twig variable expects instances of DamAsset'));
            }
        }
        return UrlHelper::actionUrl('/dam/download/', [
            'files' => $ids
        ]);
    }
}
