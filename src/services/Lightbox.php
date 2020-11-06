<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\services;

use sitemill\dam\helpers\Validation;
use sitemill\dam\Library;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use yii\base\InvalidConfigException;
use yii\base\UserException;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class Lightbox extends Component
{

    /**
     * @var string The handle of the lightboxes section
     */
    public string $lightboxSectionHandle = '';

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();
        // Set the handles from settings
        $this->lightboxSectionHandle = Library::$plugin->settings->lightboxesHandle;
    }


    /*
     * Adds an asset to a lightbox, creates a new one if required.
     *
     * @param int $assetId
     * @param int $lightboxId
     * @param string $lightboxTitle
     * @return bool
     */
    public function addAssetToLightbox(int $assetId, int $lightboxId, string $lightboxTitle = ''): bool
    {
        // If lightbox id is 0 then we'll make a lightbox
        if (!$lightboxId) {
            $lightboxId = $this->createLightbox($lightboxTitle);
        }

        $this->checkPermissions($lightboxId, $this->lightboxSectionHandle, 'edit');

        $lightbox = Craft::$app->entries->getEntryById($lightboxId);
        $currentAssets = $lightbox->getFieldValue('libraryAssets');

        $lightboxAssets = $this->getAssetIds($currentAssets);
        $lightboxAssets[] = $assetId;

        $lightbox->setFieldValue('libraryAssets', $lightboxAssets);

        return Craft::$app->elements->saveElement($lightbox);
    }


    /*
     * Creates a new lightbox in the section defined in setting
     *
     * @param string $title
     * @param int $entryType
     * @return bool
     */
    public function createLightbox(string $title)
    {
        $this->checkPermissions(null, $this->lightboxSectionHandle, 'create');

        $lightboxSection = Craft::$app->sections->getSectionByHandle($this->lightboxSectionHandle);

        // Create the lightbox
        $lightbox = new Entry([
            'sectionId' => $lightboxSection->id,
            'typeId' => 1,
            'title' => $title
        ]);

        Craft::$app->elements->saveElement($lightbox);

        return $lightbox->id;
    }


    /*
     * Deletes a lightbox in the section defined in setting
     *
     * @param int $lightboxId
     * @return bool
     */
    public function deleteLightbox(int $lightboxId): bool
    {
        $this->checkPermissions($lightboxId, $this->lightboxSectionHandle, 'delete');

        try {
            Craft::$app->elements->deleteElementById($lightboxId);
        } catch (\Throwable $e) {
            $e->getMessage();
        }

        return true;
    }


    /*
     * Removes an asset from a lightbox
     *
     * @param int $assetId
     * @param int $lightboxId
     * @return bool
     */
    public function removeAssetFromLightbox(int $assetId, int $lightboxId): bool
    {

        $this->checkPermissions($assetId, $this->lightboxSectionHandle, 'edit');

        $lightbox = Craft::$app->entries->getEntryById($lightboxId);

        $lightboxAssets = $this->getAssetIds($lightbox->getFieldValue('libraryAssets'));

        if (($key = array_search($assetId, $lightboxAssets, true)) !== false) {
            unset($lightboxAssets[$key]);
        }

        $lightbox->setFieldValue('libraryAssets', $lightboxAssets);

        return Craft::$app->elements->saveElement($lightbox);
    }


    /**
     * Helper to get the current asset IDs from an asset field
     */
    public function getAssetIds($assets)
    {
        $assetIds = [];
        foreach ($assets as $asset) {
            $assetIds[] = array_push($assetIds, $asset->id);
        }
        return $assetIds;
    }

    /**
     * Checks whether current user can perform action on requested lightbox
     *
     * @param int|null $entryId
     * @param string $sectionHandle
     * @param string $operationType
     * @return mixed
     * @throws UserException
     */
    public function checkPermissions(int $entryId = null, string $sectionHandle = '', string $operationType = '')
    {
        if ($operationType && $sectionHandle) {
            Validation::checkSectionPermissions($sectionHandle, $operationType);
        }
        if ($entryId) {
            Validation::checkEntryOwnership($entryId);
        }
        return true;
    }


    /**
     * Toggles lightbox privacy
     *
     * @param int $lightboxId
     * @return bool
     */
    public function togglePrivacy(int $lightboxId): bool
    {
        $this->checkPermissions($lightboxId, $this->lightboxSectionHandle, 'edit');

        $lightbox = Craft::$app->entries->getEntryById($lightboxId);

        $currentValue = $lightbox->getFieldValue('libraryPublic');
        $lightbox->setFieldValue('libraryPublic', !$currentValue);

        try {
            Craft::$app->elements->saveElement($lightbox);
        } catch (\Throwable $e) {
            $e->getMessage();
        }

        return true;
    }

}