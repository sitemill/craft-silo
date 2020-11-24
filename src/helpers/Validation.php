<?php


namespace sitemill\silo\helpers;


use Craft;
use yii\base\UserException;

/**
 * Class Validation
 *
 * @package sitemill\library\helpers
 */
class Validation
{
    /**
     * Checks whether current user can perform action on requested section
     *
     * @param string $sectionHandle
     * @param string $operationType
     * @return mixed
     * @throws UserException
     */
    public static function checkSectionPermissions(string $sectionHandle, string $operationType)
    {

        $section = Craft::$app->sections->getSectionByHandle($sectionHandle);
        $sectionUid = $section->uid;
        $sectionName = $section->name;

        if (!Craft::$app->user->checkPermission($operationType . 'Entries:' . $sectionUid)) {
            throw new UserException(Craft::t('library','You do not have permission to ' . $operationType . ' ' . $sectionName . '.'));
        }
        return true;
    }

    /**
     * Checks whether current user owns the current entry
     *
     * @param int $entryId
     * @return bool
     * @throws UserException
     */
    public static function checkEntryOwnership(int $entryId): bool
    {

        $userId = Craft::$app->getUser()->getIdentity()->id;
        $entryAuthorId = Craft::$app->entries->getEntryById($entryId)->authorId;

        if ($entryAuthorId != $userId) {
            throw new UserException(Craft::t('library','You do not have permission to modify this entry.'));
        }

        return true;
    }
}