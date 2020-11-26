<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\elements;

use sitemill\silo\elements\db\SiloAssetQuery;
use sitemill\silo\elements\actions\Approve;

use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Assets as AssetsHelper;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\base\Element;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\elements\db\ElementQueryInterface;
use craft\elements\actions\Delete;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;

/**
 * @author    SiteMill
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 */
class SiloAsset extends Element
{
    const STATUS_STAGED = 'staged';
    const STATUS_ACTIVE = 'active';

    // Public Properties
    // =========================================================================

    /**
     * @var int
     */
    public $uploaderId = 0;

    /**
     * @var int
     */
    public $approverId = 0;

    /**
     * @var int
     */
    public $assetId = 0;

    /**
     * @var string
     */
    public $kind = '';

    /**
     * @var int
     */
    public $size = 0;

    /**
     * @var bool
     */
    public $approved = 0;

    /**
     * @var bool
     */
    public $downloads = 0;

    /**
     * @var User|null
     */
    private $_uploader;

    /**
     * @var User|null
     */
    private $_approver;

    /**
     * @var Asset|null
     */
    private $_file;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('silo', 'Silo Asset');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('silo', 'Silo Assets');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_STAGED => ['label' => \Craft::t('silo', 'Staged'), 'color' => 'orange'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new SiloAssetQuery(static::class);
    }


    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $stagedCount = count(SiloAsset::find()->status('staged')->all());

        return [
            [
                'key' => 'active',
                'label' => 'Active',
                'criteria' => [
                    'status' => 'active'
                ],
                'hasThumbs' => true,
            ],
            [
                'key' => 'staged',
                'label' => 'Staged',
                'criteria' => [
                    'status' => 'staged'
                ],
                'badgeCount' => $stagedCount,
                'hasThumbs' => true
            ],
            [
                'heading' => 'Categories'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        if (!$this->approved) {
            return self::STATUS_STAGED;
        }

        return parent::getStatus();
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'uploader':
                $uploader = $this->getUploader();
                return $uploader ? Cp::elementHtml($uploader) : '';

            case 'filename':
                return Html::tag('span', Html::encode($this->file->filename), [
                    'class' => 'break-word',
                ]);

            case 'kind':
                return AssetsHelper::getFileKindLabel($this->kind);

            case 'size':
                if ($this->file->size === null) {
                    return '';
                }
                return Html::tag('span', $this->file->getFormattedSize(0), [
                    'title' => $this->file->getFormattedSizeInBytes(false),
                ]);

            case 'imageSize':
                return $this->file->getDimensions() ?? '';

            case 'width':
            case 'height':
                $size = $this->file->$attribute;
                return ($size ? $size . 'px' : '');
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'filename' => ['label' => Craft::t('app', 'Filename')],
            'size' => ['label' => Craft::t('app', 'File Size')],
            'kind' => ['label' => Craft::t('app', 'File Kind')],
            'imageSize' => ['label' => Craft::t('app', 'Dimensions')],
            'width' => ['label' => Craft::t('app', 'Image Width')],
            'height' => ['label' => Craft::t('app', 'Image Height')],
            'downloads' => \Craft::t('silo', 'Downloads'),
            'uploader' => \Craft::t('silo', 'Uploader'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function prepElementQueryForTableAttribute(ElementQueryInterface $elementQuery, string $attribute)
    {
        if ($attribute === 'uploader') {
            $elementQuery->andWith('uploader');
        } else {
            parent::prepElementQueryForTableAttribute($elementQuery, $attribute);
        }
    }

    public function getThumbUrl(int $size)
    {
        if ($this->file) {
            return $this->file->getThumbUrl($size);
        }
        return false;
    }

    protected static function defineActions(string $source = null): array
    {
//        TODO: check permissions before registering
        return [
            Delete::class,
            Restore::class,
            SetStatus::class,
            Approve::class
        ];
    }



    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {

        return true;
//        TODO:
//        return \Craft::$app->user->checkPermission('edit-silo-assets:'.$this->getType()->id);
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return \Craft::$app->fields->getLayoutByType(SiloAsset::class);
    }



    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);


        $html .= parent::getEditorHtml();

        return $html;
    }



    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%silo_assets}}', [
                    'id' => $this->id,
                    'uploaderId' => (int)$this->uploaderId,
                    'assetId' => (int)$this->assetId,
                    'kind' => $this->kind,
                    'size' => $this->size ?: null,
                    'approved' => $this->approved,
                    'downloads' => $this->downloads
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%silo_assets}}', [
                    'uploaderId' => (int)$this->uploaderId,
//                    'approverId' => (int)$this->approverId,
                    'assetId' => (int)$this->assetId,
                    'kind' => $this->kind,
                    'size' => $this->size ?: null,
                    'approved' => $this->approved,
                    'downloads' => $this->downloads
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterMoveInStructure(int $structureId)
    {
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {

        // The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
        $path = 'admin/silo-assets/' . $this->getSourceId() .
            ($this->slug && strpos($this->slug, '__') !== 0 ? '-' . $this->slug : '');

        $params = [];
        if (Craft::$app->getIsMultiSite()) {
            $params['site'] = $this->getSite()->handle;
        }

        return UrlHelper::cpUrl($path, $params);
    }

    public function getIsDeletable(): bool
    {
        // TODO: Implement getIsDeletable() method.
        return true;
    }


    /**
     * Eager load asset
     */
    public static function eagerLoadingMap(array $sourceElements, string $handle)
    {
        if ($handle === 'file') {
            // get the source element IDs
            $sourceElementIds = ArrayHelper::getColumn($sourceElements, 'id');

            $map = (new Query())
                ->select(['id as source', 'assetId as target'])
                ->from(['{{%silo_assets}}'])
                ->where(['and', ['id' => $sourceElementIds], ['not', ['assetId' => null]]])
                ->one();

            return [
                'elementType' => Asset::class,
                'map' => $map
            ];
        }

        if ($handle === 'uploader') {
            // Get the source element IDs
            $sourceElementIds = ArrayHelper::getColumn($sourceElements, 'id');

            $map = (new Query())
                ->select(['id as source', 'uploaderId as target'])
                ->from([Table::ASSETS])
                ->where(['and', ['id' => $sourceElementIds], ['not', ['uploaderId' => null]]])
                ->all();

            return [
                'elementType' => User::class,
                'map' => $map
            ];
        }

        return parent::eagerLoadingMap($sourceElements, $handle);
    }

    /**
     * Returns the attached asset.
     */
    public function getFile()
    {
        if ($this->_file !== null) {
            return $this->_file;
        }

        if ($this->assetId === null) {
            return null;
        }

        if (($this->_file = Craft::$app->getAssets()->getAssetById($this->assetId)) === null) {
            // The asset is probably soft-deleted. Just pretend no uploader is set
            return null;
        }
        return $this->_file;
    }

    /**
     * Sets the attached asset.
     */
    public function setFile(Asset $asset = null)
    {
        $this->_file = $asset;
    }

    /**
     * Returns the user that uploaded the Silo asset, if known.
     */
    public function getUploader()
    {
        if ($this->_uploader !== null) {
            return $this->_uploader;
        }

        if ($this->uploaderId === null) {
            return null;
        }

        if (($this->_uploader = Craft::$app->getUsers()->getUserById($this->uploaderId)) === null) {
            // The uploader is probably soft-deleted. Just pretend no uploader is set
            return null;
        }

        return $this->_uploader;
    }

    /**
     * Sets the Silo asset's uploader.
     */
    public function setUploader(User $uploader = null)
    {
        $this->_uploader = $uploader;
    }

    /**
     * Returns the user that approved the Silo asset, if known.
     */
    public function getApprover()
    {
        if ($this->_approver !== null) {
            return $this->_approver;
        }

        if ($this->approverId === null) {
            return null;
        }

        if (($this->_approver = Craft::$app->getUsers()->getUserById($this->approverId)) === null) {
            // The uploader is probably soft-deleted. Just pretend no uploader is set
            return null;
        }

        return $this->_approver;
    }

    /**
     * Sets the Silo asset's approver.
     */
    public function setApprover(User $uploader = null)
    {
        $this->_approver = $uploader;
    }


}
