<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\variables;

use craft\helpers\UrlHelper;
use sitemill\dam\Library;

use Craft;
use Traversable;

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
//        var_dump($assets);
        $ids = [];
        if (is_array($assets)) {
            foreach ($assets as $asset) {
                $ids[] = $asset->id;
            }
        } else {
            $ids[] = $assets->id;
        }
        return UrlHelper::actionUrl('/library/download/', [
            'files' => $ids
        ]);
    }
}
