<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\variables;

use craft\elements\Asset;
use craft\helpers\UrlHelper;
use sitemill\silo\elements\SiloAsset;
use sitemill\silo\Library;

use Craft;
use Traversable;
use yii\db\Exception;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SiloVariable
{
    public function settings()
    {
        return Library::$plugin->getSettings();
    }

    public function userRegistrationEnabled()
    {
        return Craft::$app->getProjectConfig()->get('users')['allowPublicRegistration'];
    }

    public function download($assetsIds)
    {
        return UrlHelper::actionUrl('/silo/download/', [
            'files' => $assetsIds
        ]);
    }
}
