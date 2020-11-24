<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\controllers;

use craft\base\Element;
use craft\controllers\BaseEntriesController;
use craft\elements\Asset;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use sitemill\silo\elements\SiloAsset;

use Craft;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SiloAssetsController extends BaseEntriesController
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
     * @param int $siloAssetId The asset ID
     * @param siloAsset|null $siloAsset The asset being edited, if there were any validation errors.
     * @param string|null $site The site handle, if specified.
     * @return Response
     * @throws BadRequestHttpException if `$assetId` is invalid
     * @throws ForbiddenHttpException if the user isn't permitted to edit the asset
     * @since 3.4.0
     */
    public function actionEditSiloAsset(int $siloAssetId, SiloAsset $siloAsset = null, string $site = null): Response
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

        if ($siloAsset === null) {
            $siloAsset = SiloAsset::find()
                ->id($siloAssetId)
                ->siteId($site->id)
                ->status(null)
                ->one();
            if ($siloAsset === null) {
                throw new BadRequestHttpException("Invalid asset ID: {$siloAssetId}");
            }
        }

        $file = $siloAsset->file;

        // Do they have permission?
        $this->enforceEditSiloAssetPermissions($siloAsset);

        $volume = $file->getVolume();

        $crumbs = [
            [
                'label' => Craft::t('silo', 'Asset Manager'),
                'url' => UrlHelper::url('silo-assets')
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

//            TODO: investigate editable images – can I create new Silo asset from crafts editor
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

//        // TODO: Check permission on siloAsset
//        try {
//            $this->requireVolumePermissionByAsset('deleteFilesAndFoldersInVolume', $libraryAsset);
//            $this->requirePeerVolumePermissionByAsset('deletePeerFilesInVolume', $libraryAsset);
//            $canDelete = true;
//        } catch (ForbiddenHttpException $e) {
//            $canDelete = false;
//        }

        // Get field layout
        $fieldLayout = $siloAsset->getFieldLayout();
        $form = $fieldLayout->createForm($siloAsset);
        $tabs = $form->getTabMenu();
        $fieldsHtml = $form->render();

        return $this->renderTemplate('silo/silo-assets/_edit', [
            'siteId' => $site->id,
            'element' => $siloAsset,
            'volume' => $volume,
            'file' => $siloAsset->file,
            'slug' => $siloAsset->slug,
            'title' => trim($siloAsset->title) ?: Craft::t('app', 'Edit Asset'),
            'crumbs' => $crumbs,
            'previewHtml' => $previewHtml,
            'formattedSize' => $siloAsset->file->getFormattedSize(0),
            'formattedSizeInBytes' => $siloAsset->file->getFormattedSizeInBytes(false),
            'dimensions' => $siloAsset->file->getDimensions(),
            'isApproved' => $siloAsset->approved,
            'canReplaceFile' => $canReplaceFile,
            'canEdit' => $siloAsset->getIsEditable(),
            'tabs' => $tabs,
            'fieldsHtml' => $fieldsHtml,
//           TODO: Enable deleteing once template sorted
            'canDeleteSource' => 0,
        ]);
    }

    public function actionUploadSiloAssets()
    {
        // Do they have permission?
        $this->requirePermission('silo-createSiloAssets');

        $response = Craft::$app->runAction('assets/upload');

        $asset = Craft::$app->assets->getAssetById($response->data['assetId']);

        $siloAsset = new SiloAsset([
            'title' => $asset->title,
            'uploaderId' => $asset->uploaderId,
            'assetId' => $asset->id,
            'kind' => $asset->kind,
            'size' => $asset->size
        ]);

        if (!Craft::$app->elements->saveElement($siloAsset)) {
            throw new Exception("Couldn't create Silo asset");
        }

        return $this->asJson([
            'success' => true,
            'siloAssetId' => $siloAsset->id
        ]);
    }

    /**
     * Saves a Silo asset.
     *
     * @return Response|null
     * @throws ServerErrorHttpException
     */
    public function actionSaveSiloAsset()
    {
        $this->requirePostRequest();

        $siloAsset = $this->_getSiloAssetModel();

        $siloAssetVariable = $this->request->getValidatedBodyParam('siloAssetVariable') ?? 'siloAsset';

        // Do they have permission?
        $this->enforceEditSiloAssetPermissions($siloAsset);

        if (Craft::$app->getIsMultiSite()) {
            // Make sure they have access to this site
            $this->requirePermission('editSite:' . $siloAsset->getSite()->uid);
        }

//        TODO: allow renaming of linked file
//        $siloAsset->file->newFilename = $this->request->getParam('filename');

        // Populate the entry with post data
        $this->_populateEntryModel($siloAsset);

        // Save the asset
        $siloAsset->setScenario(Element::SCENARIO_LIVE);

        if (!Craft::$app->getElements()->saveElement($siloAsset)) {
            if ($this->request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $siloAsset->getErrors(),
                ]);
            }

            $this->setFailFlash(Craft::t('silo', 'Couldn’t save Silo asset.'));

            // Send the Silo asset back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                $siloAssetVariable => $siloAsset
            ]);

            return null;
        }

        if ($this->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $siloAsset->id,
                'title' => $siloAsset->title,
                'url' => $siloAsset->getUrl(),
                'cpEditUrl' => $siloAsset->getCpEditUrl()
            ]);
        }
        $this->setSuccessFlash(Craft::t('silo', 'Silo asset saved.'));
        return $this->redirectToPostedUrl($siloAsset);
    }


    /**
     * Fetches or creates a SiloAsset.
     *
     * @return SiloAsset
     * @throws BadRequestHttpException if the requested category group doesn't exist
     * @throws NotFoundHttpException if the requested category cannot be found
     */
    private function _getSiloAssetModel(): SiloAsset
    {
        $siloAssetId = $this->request->getBodyParam('sourceId');
        $siteId = $this->request->getBodyParam('siteId');

        if ($siloAssetId) {
            $siloAsset = Craft::$app->elements->getElementById($siloAssetId, SiloAsset::class, $siteId);
            if (!$siloAsset) {
                throw new NotFoundHttpException('Silo asset not found');
            }
        } else {
            $siloAsset = new SiloAsset();
            if ($siteId) {
                $siloAsset->siteId = $siteId;
            }
        }

        return $siloAsset;
    }

    /**
     * Populates a Silo Asset with post data.
     *
     * @param SiloAsset $siloAsset
     */
    private function _populateEntryModel(SiloAsset $siloAsset)
    {
        $siloAsset->slug = $this->request->getBodyParam('slug', $siloAsset->slug);

        if (($approved = $this->request->getBodyParam('approved')) !== null) {
            $siloAsset->approved = $approved ?: 0;
        }

        if (($postDate = $this->request->getBodyParam('postDate')) !== null) {
            $siloAsset->postDate = DateTimeHelper::toDateTime($postDate) ?: null;
        }
        if (($expiryDate = $this->request->getBodyParam('expiryDate')) !== null) {
            $siloAsset->expiryDate = DateTimeHelper::toDateTime($expiryDate) ?: null;
        }

        $enabledForSite = $this->enabledForSiteValue();
        if (is_array($enabledForSite)) {
            // Set the global status to true if it's enabled for *any* sites, or if already enabled.
            $siloAsset->enabled = in_array(true, $enabledForSite, false) || $siloAsset->enabled;
        } else {
            $siloAsset->enabled = (bool)$this->request->getBodyParam('enabled', $siloAsset->enabled);
        }
        $siloAsset->setEnabledForSite($enabledForSite ?? $siloAsset->getEnabledForSite());
        $siloAsset->title = $this->request->getBodyParam('title', $siloAsset->title);

        $fieldsLocation = $this->request->getParam('fieldsLocation', 'fields');
        $siloAsset->setFieldValuesFromRequest($fieldsLocation);

        // Author
        $uploaderId = $this->request->getBodyParam('author', ($siloAsset->uploaderId ?: Craft::$app->getUser()->getIdentity()->id));

        if (is_array($uploaderId)) {
            $uploaderId = $authorId[0] ?? null;
        }

        $siloAsset->uploaderId = $uploaderId;
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
     * @param SiloAsset $siloAsset
     * @param bool $duplicate
     * @throws ForbiddenHttpException
     */
    protected function enforceEditSiloAssetPermissions(SiloAsset $siloAsset, bool $duplicate = false)
    {
        // Make sure the user is allowed to edit Silo assets
        $this->requirePermission('silo-editSiloAssets');

        // Is it a new Silo asset?
        if (!$siloAsset->id || $duplicate) {
            // Make sure they have permission to create new Silo assets
            $this->requirePermission('silo-createSiloAssets');
            return;
        }

        // If not owned by them, can they edit?
        $userId = Craft::$app->getUser()->getId();
        if ($siloAsset->uploaderId != $userId) {
            $this->requirePermission('silo-editPeerSiloAssets');
        }
    }
}
