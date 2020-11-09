<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\models;

use sitemill\dam\Library;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================


    /**
     * @var string The handle of the asset volume used by Library
     */
    public $assetsHandle = null;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['assetsHandle', 'string'],
            ['assetsHandle', 'default', 'value' => 'assets']
        ];
    }
}
