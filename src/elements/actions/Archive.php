<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use sitemill\silo\elements\SiloAsset;
use sitemill\silo\Library;


/**
 * @author    SiteMill
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */

class Archive extends ElementAction
{
    /**
     * @var string|null The message that should be shown after the elements get restored
     */
    public $successMessage;

    /**
     * @var string|null The message that should be shown after some elements get restored
     */
    public $partialSuccessMessage;

    /**
     * @var string|null The message that should be shown if no elements get restored
     */
    public $failMessage;

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('silo', 'Archive');
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        return '<div class="btn formsubmit">' . $this->getTriggerLabel() . '</div>';
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $anySuccess = false;
        $anyFail = false;

        foreach ($query->all() as $element) {
           $libraryAsset = Library::$plugin->libraryAssets->getLibraryAssetById($element->id);
           Library::$plugin->libraryAssets->archiveLibraryAsset($libraryAsset);
        }

        return true;
    }

}