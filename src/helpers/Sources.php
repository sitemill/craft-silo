<?php


namespace sitemill\silo\helpers;

use Craft;
use craft\volumes\Local;
use yii\base\Exception;

/**
 * Class Sources
 *
 * @package sitemill\library\helpers
 */
class Sources
{
    /**
     * Creates a field group
     *
     * @param string $groupHandle
     * @throws \Exception
     */
    public static function createFieldGroup(string $groupHandle)
    {
        if (!self::getFieldGroup($groupHandle)) {
            // Set the field group name
            $group = new \craft\models\FieldGroup([
                "name" => $groupHandle,
            ]);

            // Save the group
            try {
                $group = Craft::$app->fields->saveGroup($group);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
        return self::getFieldGroup($groupHandle);
    }

    /**
     * Finds the ID for the given field group handle
     *
     * @param string $fieldGroupHandle
     * @returrn mixed
     */
    public static function getFieldGroup(string $fieldGroupHandle)
    {
        // Do a database query to return group ID
        $group = (new \craft\db\Query())
            ->select("id")
            ->from("fieldgroups")
            ->where(["name" => $fieldGroupHandle ?: 'common']);
        if ($group->one()) {
            return $group->one()["id"];
        }
        return false;
    }

    /**
     * Creates an asset volume
     *
     * @param string $volumeHandle
     * @param string $volumeName
     */
    public static function createAssetVolume(string $volumeHandle, string $volumeName)
    {
        if (!Craft::$app->getVolumes()->getVolumeByHandle($volumeHandle)) {

            $folder = strtolower(preg_replace('%([A-Z])([a-z])%', '-\1\2', $volumeHandle));

            $volume = new Local([
                'name' => $volumeName,
                'handle' => $volumeHandle,
                'hasUrls' => true,
                'path' => CRAFT_BASE_PATH . DIRECTORY_SEPARATOR . 'web/' . $folder,
                'url' => Craft::getAlias('@web') . DIRECTORY_SEPARATOR . $folder,
            ]);

            try {
                Craft::$app->volumes->saveVolume($volume);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
        // Get the folder to save files to
        $volume = Craft::$app->volumes->getVolumeByHandle($volumeHandle);
        $folders = Craft::$app->assets->getFolderTreeByVolumeIds([$volume->id]);
        return $folders[0]->uid;
    }

    /**
     * Creates a tags group
     *
     * @param string $tagsGroupHandle
     * @param string $tagsGroupName
     * @return string
     */
    public static function createTagsGroup(string $tagsGroupHandle, string $tagsGroupName)
    {
        if (!Craft::$app->tags->getTagGroupByHandle($tagsGroupHandle)) {
            $tagGroup = new \craft\models\TagGroup([
                "name" => $tagsGroupName,
                "handle" => $tagsGroupHandle
            ]);
            try {
                Craft::$app->tags->saveTagGroup($tagGroup);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
        return Craft::$app->tags->getTagGroupByHandle($tagsGroupHandle)->uid;
    }

    /**
     * Creates a Categories group
     **
     *
     * @param string $categoryGroupHandle
     * @param string $categoryGroupName
     * @return string
     */
    public static function createCategoryGroup(string $categoryGroupHandle, string $categoryGroupName): string
    {
        if (!Craft::$app->categories->getGroupByHandle($categoryGroupHandle)) {
            $siteId = Craft::$app->sites->getPrimarySite()->id;

            // TODO: add variable for template route
            $categoryGroup = new \craft\models\CategoryGroup([
                "name" => $categoryGroupName,
                "handle" => $categoryGroupHandle
            ]);

            $settings = [
                "siteId" => $siteId,
                "uriFormat" => '/category/{id}/{slug}',
                "template" => '_pages/category',
                "hasUrls" => 1
            ];

            $categorySettings = new \craft\models\CategoryGroup_SiteSettings($settings);

            $categoryGroup->setSiteSettings([$siteId => $categorySettings]);

            try {
                Craft::$app->categories->saveGroup($categoryGroup);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }

        return Craft::$app->categories->getGroupByHandle($categoryGroupHandle)->uid;
    }

}