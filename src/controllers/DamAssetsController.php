<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam\controllers;

use craft\base\Element;
use craft\controllers\BaseEntriesController;
use craft\elements\Asset;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use sitemill\dam\elements\DamAsset;

use Craft;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 */
class DamAssetsController extends BaseEntriesController
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    public $enableCsrfValidation = false;

    /**
     * @return mixed
     */
    /**
     * Edits an asset.
     *
     * @param int $damAssetId The asset ID
     * @param damAsset|null $damAsset The asset being edited, if there were any validation errors.
     * @param string|null $site The site handle, if specified.
     * @return Response
     * @throws BadRequestHttpException if `$assetId` is invalid
     * @throws ForbiddenHttpException if the user isn't permitted to edit the asset
     * @since 3.4.0
     */
    public function actionEditDamAsset(int $damAssetId, DamAsset $damAsset = null, string $site = null): Response
    {
        $sitesService = Craft::$app->getSites();
        $editableSiteIds = $sitesService->getEditableSiteIds();
        if ($site !== null) {
            $siteHandle = $site;
            $site = $sitesService->getSiteByHandle($siteHandle);
            if (!$site) {
                throw new BadRequestHttpException("Invalid site handle: {$siteHandle}");
            }
            if (!in_array($site->id, $editableSiteIds, false)) {
                throw new ForbiddenHttpException('User not permitted to edit content in this site');
            }
        } else {
            $site = $sitesService->getCurrentSite();
            if (!in_array($site->id, $editableSiteIds, false)) {
                $site = $sitesService->getSiteById($editableSiteIds[0]);
            }
        }

        if ($damAsset === null) {
            $damAsset = DamAsset::find()
                ->id($damAssetId)
                ->siteId($site->id)
                ->status(null)
                ->one();
            if ($damAsset === null) {
                throw new BadRequestHttpException("Invalid asset ID: {$damAssetId}");
            }
        }

        $file = $damAsset->file;

        // Do they have permission?
        $this->enforceEditDamAssetPermissions($damAsset);

        $volume = $file->getVolume();

        $crumbs = [
            [
                'label' => Craft::t('dam', 'Asset Manager'),
                'url' => UrlHelper::url('dam-assets')
            ]
        ];

        // Show thumbnail
        try {
            // Is the image editable, and is the user allowed to edit?
            $userSession = Craft::$app->getUser();

            $editable = (
                $file->getSupportsImageEditor() &&
                $userSession->checkPermission("editImagesInVolume:{$volume->uid}") &&
                ($userSession->getId() == $file->uploaderId || $userSession->checkPermission("editPeerImagesInVolume:{$volume->uid}"))
            );

            $previewHtml = '<div id="preview-thumb-container" class="preview-thumb-container">' .
                '<div class="preview-thumb">' .
                $file->getPreviewThumbImg(350, 190) .
                '</div>' .
                '<div class="buttons">';

            if (Craft::$app->getAssets()->getAssetPreviewHandler($file) !== null) {
                $previewHtml .= '<div class="btn" id="preview-btn">' . Craft::t('app', 'Preview') . '</div>';
            }

//            TODO: investigate editable images – can I create new DAM asset from crafts editor
//            if ($editable) {
//                $previewHtml .= '<div class="btn" id="edit-btn">' . Craft::t('app', 'Edit') . '</div>';
//            }

            $previewHtml .= '</div></div>';
        } catch (NotSupportedException $e) {
            // NBD
            $previewHtml = '';
        }

//        TODO: not sure about this re: permissions
        $userSession = Craft::$app->getUser();
        $canReplaceFile = (
            $userSession->checkPermission("deleteFilesAndFoldersInVolume:{$volume->uid}") &&
            ($userSession->getId() == $file->uploaderId || $userSession->checkPermission("replacePeerFilesInVolume:{$volume->uid}"))
        );

//        // TODO: Check permission on damAsset
//        try {
//            $this->requireVolumePermissionByAsset('deleteFilesAndFoldersInVolume', $libraryAsset);
//            $this->requirePeerVolumePermissionByAsset('deletePeerFilesInVolume', $libraryAsset);
//            $canDelete = true;
//        } catch (ForbiddenHttpException $e) {
//            $canDelete = false;
//        }

        // Get field layout
        $fieldLayout = $damAsset->getFieldLayout();
        $form = $fieldLayout->createForm($damAsset);
        $tabs = $form->getTabMenu();
        $fieldsHtml = $form->render();

        return $this->renderTemplate('dam/dam-assets/_edit', [
            'siteId' => $site->id,
            'element' => $damAsset,
            'volume' => $volume,
            'file' => $damAsset->file,
            'slug' => $damAsset->slug,
            'title' => trim($damAsset->title) ?: Craft::t('app', 'Edit Asset'),
            'crumbs' => $crumbs,
            'previewHtml' => $previewHtml,
            'formattedSize' => $damAsset->file->getFormattedSize(0),
            'formattedSizeInBytes' => $damAsset->file->getFormattedSizeInBytes(false),
            'dimensions' => $damAsset->file->getDimensions(),
            'isApproved' => $damAsset->approved,
            'canReplaceFile' => $canReplaceFile,
            'canEdit' => $damAsset->getIsEditable(),
            'tabs' => $tabs,
            'fieldsHtml' => $fieldsHtml,
//           TODO: Enable deleteing once template sorted
            'canDeleteSource' => 0,
        ]);
    }

    public function actionUploadDamAssets()
    {
        // Do they have permission?
        $this->requirePermission('dam-createDamAssets');

        $response = Craft::$app->runAction('assets/upload');

        $asset = Craft::$app->assets->getAssetById($response->data['assetId']);

        $damAsset = new DamAsset([
            'title' => $asset->title,
            'uploaderId' => $asset->uploaderId,
            'assetId' => $asset->id,
            'filename' => $asset->filename,
            'kind' => $asset->kind,
            'width' => $asset->width,
            'height' => $asset->height,
            'size' => $asset->size,
            'focalPoint' => $asset->hasFocalPoint ? $asset->focalPoint : null,
        ]);

        if (!Craft::$app->elements->saveElement($damAsset)) {
            throw new Exception("Couldn't create DAM asset");
        }

        return $this->asJson([
            'success' => true,
            'damAssetId' => $damAsset->id
        ]);
    }

    /**
     * Saves a DAM asset.
     *
     * @return Response|null
     * @throws ServerErrorHttpException
     */
    public function actionSaveDamAsset()
    {
        $this->requirePostRequest();

        $damAsset = $this->_getDamAssetModel();

        $damAssetVariable = $this->request->getValidatedBodyParam('damAssetVariable') ?? 'damAsset';

        // Do they have permission?
        $this->enforceEditDamAssetPermissions($damAsset);

        if (Craft::$app->getIsMultiSite()) {
            // Make sure they have access to this site
            $this->requirePermission('editSite:' . $damAsset->getSite()->uid);
        }

//        TODO: allow renaming of linked file
//        $damAsset->file->newFilename = $this->request->getParam('filename');

        // Populate the entry with post data
        $this->_populateEntryModel($damAsset);

        // Save the asset
        $damAsset->setScenario(Element::SCENARIO_LIVE);

        if (!Craft::$app->getElements()->saveElement($damAsset)) {
            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $damAsset->getErrors(),
                ]);
            }

            $this->setFailFlash(Craft::t('dam', 'Couldn’t save DAM asset.'));

            // Send the DAM asset back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                $damAssetVariable => $damAsset
            ]);

            return null;
        }

        if ($this->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $damAsset->id,
                'title' => $damAsset->title,
                'url' => $damAsset->getUrl(),
                'cpEditUrl' => $damAsset->getCpEditUrl()
            ]);
        }
        $this->setSuccessFlash(Craft::t('dam', 'DAM asset saved.'));
        return $this->redirectToPostedUrl($damAsset);
    }


    /**
     * Fetches or creates a DamAsset.
     *
     * @return DamAsset
     * @throws BadRequestHttpException if the requested category group doesn't exist
     * @throws NotFoundHttpException if the requested category cannot be found
     */
    private function _getDamAssetModel(): DamAsset
    {
        $damAssetId = $this->request->getBodyParam('sourceId');
        $siteId = $this->request->getBodyParam('siteId');

        if ($damAssetId) {
            $damAsset = Craft::$app->elements->getElementById($damAssetId,DamAsset::class, $siteId);
            if (!$damAsset) {
                throw new NotFoundHttpException('DAM asset not found');
            }
        } else {
            $damAsset = new DamAsset();
            if ($siteId) {
                $damAsset->siteId = $siteId;
            }
        }

        return $damAsset;
    }

    /**
     * Populates a DAM Asset with post data.
     *
     * @param DamAsset $damAsset
     */
    private function _populateEntryModel(DamAsset $damAsset)
    {
        $damAsset->slug = $this->request->getBodyParam('slug', $damAsset->slug);

        if (($approved = $this->request->getBodyParam('approved')) !== null) {
            $damAsset->approved = $approved ?: 0;
        }

        if (($postDate = $this->request->getBodyParam('postDate')) !== null) {
            $damAsset->postDate = DateTimeHelper::toDateTime($postDate) ?: null;
        }
        if (($expiryDate = $this->request->getBodyParam('expiryDate')) !== null) {
            $damAsset->expiryDate = DateTimeHelper::toDateTime($expiryDate) ?: null;
        }

        $enabledForSite = $this->enabledForSiteValue();
        if (is_array($enabledForSite)) {
            // Set the global status to true if it's enabled for *any* sites, or if already enabled.
            $damAsset->enabled = in_array(true, $enabledForSite, false) || $damAsset->enabled;
        } else {
            $damAsset->enabled = (bool)$this->request->getBodyParam('enabled', $damAsset->enabled);
        }
        $damAsset->setEnabledForSite($enabledForSite ?? $damAsset->getEnabledForSite());
        $damAsset->title = $this->request->getBodyParam('title', $damAsset->title);

        $fieldsLocation = $this->request->getParam('fieldsLocation', 'fields');
        $damAsset->setFieldValuesFromRequest($fieldsLocation);

        // Author
        $uploaderId = $this->request->getBodyParam('author', ($damAsset->uploaderId ?: Craft::$app->getUser()->getIdentity()->id));

        if (is_array($uploaderId)) {
            $uploaderId = $authorId[0] ?? null;
        }

        $damAsset->uploaderId = $uploaderId;

    }

    /**
     * Returns the posted `enabledForSite` value, taking the user’s permissions into account.
     *
     * @return bool|bool[]|null
     * @throws ForbiddenHttpException
     * @since 3.4.0
     */
    protected function enabledForSiteValue()
    {
        $enabledForSite = $this->request->getBodyParam('enabledForSite');
        if (is_array($enabledForSite)) {
            // Make sure they are allowed to edit all of the posted site IDs
            $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();
            if (array_diff(array_keys($enabledForSite), $editableSiteIds)) {
                throw new ForbiddenHttpException('User not permitted to edit the statuses for all the submitted site IDs');
            }
        }
        return $enabledForSite;
    }

    /**
     * @return mixed
     */
    public function actionEditFieldLayout()
    {
//      TODO: change this controller to edit settings, have field layout on same page as settings
        return $this->renderTemplate('dam/settings/dam-assets');
    }


    /**
     * @return mixed
     */
    public function actionSaveFieldLayout()
    {
        $this->requirePostRequest();

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = DamAsset::class;
        if (!Craft::$app->fields->saveLayout($fieldLayout)) {
            $this->setFailFlash(Craft::t('app', 'Couldn’t save field layout.'));
            return null;
        }
        $this->setSuccessFlash(Craft::t('app', 'Field layout saved.'));
        return true;
    }

    /**
     * @param DamAsset $damAsset
     * @param bool $duplicate
     * @throws ForbiddenHttpException
     */
    protected function enforceEditDamAssetPermissions(DamAsset $damAsset, bool $duplicate = false)
    {
        // Make sure the user is allowed to edit DAM assets
        $this->requirePermission('dam-editDamAssets');

        // Is it a new DAM asset?
        if (!$damAsset->id || $duplicate) {
            // Make sure they have permission to create new DAM assets
            $this->requirePermission('dam-createDamAssets');
            return;
        }

        // If not owned by them, can they edit?
        $userId = Craft::$app->getUser()->getId();
        if ($damAsset->uploaderId != $userId) {
            $this->requirePermission('dam-editPeerDamAssets');
        }
    }
}
