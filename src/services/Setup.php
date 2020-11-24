<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo\services;

use craft\errors\EntryTypeNotFoundException;
use sitemill\silo\Library;
use sitemill\silo\helpers\Sources;

use Craft;
use craft\base\Component;
use yii\base\Exception;

/**
 * @author    SiteMill
 * @package   Silo
 * @since     1.0.0
 *
 */
class Setup extends Component
{
    // TODO: make template folder a variable, and set with sections

    // Public Properties
    // =========================================================================

    /**
     * @var string The handle of the asset volume used by Library
     */
    public string $assetsHandle = '';

    /**
     * @var string The handle of the pages section used by Library
     */
    public string $pagesHandle = '';

    /**
     * @var string The handle of the category group used by Library
     */
    public string $categoriesHandle = '';

    /**
     * @var string The handle of the lightbox section used by Library
     */
    public string $lightboxesHandle = '';

    /**
     * @var string The field group used for Library
     */
    public string $fieldGroupName = 'Library';

    /**
     * @var int The field group used for Library
     */
    public int $fieldGroupId = 1;


    /**
     * @var mixed The UID of the lightbox section
     */
    public $lightboxesSectionUid = '';

    /**
     * @var string The UID of the lightbox section
     */
    public string $assetsVolumeUid = '';

    /**
     * @var string Handle of the tags group
     */
    public string $tagsGroupName = 'Library';

    /**
     * @var string Handle of the tags group
     */
    public string $tagsGroupHandle = 'libraryTags';

    /**
     * @var string Uid of the tags group
     */
    public string $tagsGroupUid = '';

    /**
     * @var string Uid of the category group
     */
    public string $categoryGroupUid = '';

    // Public Methods
    // =========================================================================

    /*
     * @return null
     */
    public function init()
    {
        parent::init();
        // Set the handles from settings
        $this->assetsHandle = Library::$plugin->settings->assetsHandle;
        $this->pagesHandle = Library::$plugin->settings->pagesHandle;
        $this->categoriesHandle = Library::$plugin->settings->categoriesHandle;
        $this->lightboxesHandle = Library::$plugin->settings->lightboxesHandle;
    }

    public function getFields()
    {
        // Define all the fields needed
        // TODO: Add translations
        // TODO: Add searchable
        return [
            [
                'type' => 'craft\\fields\\Assets',
                'handle' => 'libraryAssets',
                'name' => 'Assets',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    "defaultUploadLocationSource" => "folder:" . $this->assetsVolumeUid,
                    "defaultUploadLocationSubpath" => "",
                    "singleUploadLocationSource" => "folder:" . $this->assetsVolumeUid,
                    "singleUploadLocationSubpath" => "",
                    "sources" => "folder:" . $this->assetsVolumeUid,
                    "source" => null,
                    'viewMode' => 'large',
                    'useSingleFolder' => 1
                ]
            ],
            [
                'type' => 'craft\\fields\\Plaintext',
                'handle' => 'libraryDescription',
                'name' => 'Description',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    'required' => false,
                    'multiline' => true,
                    'initialRows' => 4
                ]
            ],
            [
                'type' => 'craft\\fields\\Tags',
                'handle' => 'libraryTags',
                'name' => 'Tags',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    "source" => "taggroup:" . $this->tagsGroupUid
                ]
            ],
            [
                'type' => 'craft\\fields\\Categories',
                'handle' => 'libraryCategories',
                'name' => 'Categories',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    'selectionLabel' => 'Select a Category',
                    'source' => 'group:' . $this->categoryGroupUid
                ]
            ],
            [
                'type' => 'craft\\fields\\Lightswitch',
                'handle' => 'libraryPublic',
                'name' => 'Public',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    'onLabel' => 'Public',
                    'offLabel' => 'Private',
                    'instructions' => 'Make this page accessible to the public'
                ]
            ],
            [
                'type' => 'craft\\fields\\Matrix',
                'handle' => 'libraryPageContent',
                'name' => 'Page content',
                'groupId' => $this->fieldGroupId,
                'settings' => [
                    'instructions' => 'Make this page accessible to the public',
                    'minBlocks' => '',
                    'maxBlocks' => '',
                    'localizeBlocks' => false,
                ],
                'blockTypes' => [
                    'new1' => [
                        'name' => 'Heading',
                        'handle' => 'heading',
                        'fields' => [
                            'new1' => [
                                'type' => '\\craft\\fields\\PlainText',
                                'handle' => 'pageContentHeading',
                                'width' => 75
                            ],
                            'new2' => [
                                'type' => '\\craft\\fields\\Dropdown',
                                'handle' => 'pageContentHeadingType',
                                'width' => 25,
                                'typesettings' => [
                                    'options' => [
                                        ['label' => 'Heading 2', 'value' => 'h2', 'default' => 1],
                                        ['label' => 'Heading 3', 'value' => 'h3', 'default' => ''],
                                        ['label' => 'Heading 4', 'value' => 'h4', 'default' => '']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'new2' => [
                        'name' => 'Text',
                        'handle' => 'text',
                        'fields' => [
                            'new1' => [
                                'type' => '\\craft\\fields\\PlainText',
                                'handle' => 'pageContentText',
                                'typesettings' => [
                                    'multiline' => true,
                                    'initialRows' => 4
                                ]
                            ]
                        ]
                    ],
                    'new3' => [
                        'name' => 'Assets',
                        'handle' => 'assets',
                        'fields' => [
                            'new1' => [
                                'type' => '\\craft\\fields\\Dropdown',
                                'handle' => 'pageContentAssetsStyle',
                                'name' => 'Layout style',
                                'typesettings' => [
                                    'options' => [
                                        ['label' => 'Default', 'value' => 'default', 'default' => 1],
                                        ['label' => 'Grid', 'value' => 'grid', 'default' => ''],
                                        ['label' => 'List', 'value' => 'list', 'default' => ''],
                                        ['label' => 'Slideshow', 'value' => 'slideshow', 'default' => '']
                                    ]
                                ]
                            ],
                            'new2' => [
                                'type' => '\\craft\\fields\\Assets',
                                'handle' => 'pageContentAssets',
                                'required' => true,
                                'typesettings' => [
                                    "defaultUploadLocationSource" => "folder:" . $this->assetsVolumeUid,
                                    "defaultUploadLocationSubpath" => "",
                                    "sources" => "folder:" . $this->assetsVolumeUid,
                                    "source" => null,
                                    'viewMode' => 'large'
                                ]
                            ]
                        ]
                    ],
                    'new4' => [
                        'name' => 'Lightbox',
                        'handle' => 'lightbox',
                        'fields' => [
                            'new1' => [
                                'type' => '\\craft\\fields\\Entries',
                                'handle' => 'pageContentLightbox',
                                'instructions' => 'Embed a lightbox',
                                'required' => true,
                                'typesettings' => [
                                    "limit" => "1",
                                    "selectionLabel" => "Select lightbox",
                                    "sources" => ["section:" . $this->lightboxesSectionUid]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /*
     * @return mixed
     */
    public function setupRequirementsCheck()
    {
//        TODO: Check that we can setup, ie is CRAFT_BASE_PATH set?
    }

    /*
     * @return mixed
     */
    public function run()
    {
        // Setup the sections
        $this->createPagesSection();
        $this->createLightboxesSection();

        // Setup field group
        $this->fieldGroupId = Sources::createFieldGroup($this->fieldGroupName);

        // Create category group
        $this->categoryGroupUid = Sources::createCategoryGroup($this->categoriesHandle, "Categories");

        // Setup the asset volume
        $this->assetsVolumeUid = Sources::createAssetVolume($this->assetsHandle, "Library Assets");

        // Setup tags group
        $this->tagsGroupUid = Sources::createTagsGroup($this->tagsGroupHandle, $this->tagsGroupName);

        echo $this->lightboxesSectionUid;

        // Go on sausage
        foreach ($this->getFields() as &$field) {
            $field = Craft::$app->getFields()->createField($field);
            try {
                Craft::$app->getFields()->saveField($field);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }


        $this->setupPagesFields();
        $this->setupLightboxesFields();
    }

    /*
     * @return mixed
     */
    public function createLightboxesSection(): bool
    {
        if (!Craft::$app->sections->getSectionByHandle($this->lightboxesHandle)) {
            // Initialize the section
            $lightboxesSection = new \craft\models\Section([
                "name" => "Lightboxes",
                "handle" => $this->lightboxesHandle,
                "type" => \craft\models\Section::TYPE_CHANNEL,
                "enableVersioning" => true,
                "propagateEntries" => true,
                "siteSettings" => [
                    new \craft\models\Section_SiteSettings([
                        "siteId" => Craft::$app->sites->getPrimarySite()->id,
                        "enabledByDefault" => true,
                        "hasUrls" => true,
                        "uriFormat" => "lightbox/{id}/{slug}",
                        "template" => "_pages/lightbox",
                    ]),
                ],
            ]);

            try {
                Craft::$app->sections->saveSection($lightboxesSection);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }

        // Set lightbox section Uid
        $lightboxSection = Craft::$app->sections->getSectionByHandle($this->lightboxesHandle);
        $this->lightboxesSectionUid = $lightboxSection->uid;

        return true;
    }


    /*
    * @return mixed
    */
    public function setupLightboxesFields(): bool
    {
        $entries = Craft::$app->sections->getEntryTypesByHandle($this->lightboxesHandle);
        $lightboxEntry = $entries[0];

        // Get the current field layout
        $fieldLayout = $lightboxEntry->getFieldLayout();

        // Main tab
        $mainTab = new \craft\models\FieldLayoutTab(["name" => "Main"]);

        // Select all the fields for the main tab
        $description = Craft::$app->fields->getFieldByHandle("libraryDescription");
        $assets = Craft::$app->fields->getFieldByHandle("libraryAssets");
        $public = Craft::$app->fields->getFieldByHandle("libraryPublic");

        // Set the fields
        $mainTab->setFields([$description, $assets, $public]);

        // Set the tabs on the field layout
        $fieldLayout->setTabs([$mainTab]);

        // And finally save the field layout
        return (Craft::$app->fields->saveLayout($fieldLayout) && Craft::$app->sections->saveEntryType($lightboxEntry));
    }

    /*
     * @return mixed
     */
    public function createPagesSection(): bool
    {
        if (!Craft::$app->sections->getSectionByHandle($this->lightboxesHandle)) {
            // Initialize the section
            $pagesSection = new \craft\models\Section([
                "name" => "Pages",
                "handle" => $this->pagesHandle,
                "type" => \craft\models\Section::TYPE_CHANNEL,
                "enableVersioning" => true,
                "propagateEntries" => true,
                "siteSettings" => [
                    new \craft\models\Section_SiteSettings([
                        "siteId" => Craft::$app->sites->getPrimarySite()->id,
                        "enabledByDefault" => true,
                        "hasUrls" => true,
                        "uriFormat" => "page/{id}/{slug}",
                        "template" => "_pages/page",
                    ]),
                ],
            ]);

            try {
                Craft::$app->sections->saveSection($pagesSection);
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
        return true;
    }

    /*
    * @return mixed
    */
    public function setupPagesFields(): bool
    {
        $entries = Craft::$app->sections->getEntryTypesByHandle($this->pagesHandle);
        $pagesEntry = $entries[0];

        // Get the current field layout
        $fieldLayout = $pagesEntry->getFieldLayout();

        // Main tab
        $mainTab = new \craft\models\FieldLayoutTab(["name" => "Main"]);

        // Select all the fields for the main tab
        $description = Craft::$app->fields->getFieldByHandle("libraryDescription");
        $pageContent = Craft::$app->fields->getFieldByHandle("libraryPageContent");
        $public = Craft::$app->fields->getFieldByHandle("libraryPublic");

        // Set the fields
        $mainTab->setFields([$description, $pageContent, $public]);

        // Set the tabs on the field layout
        $fieldLayout->setTabs([$mainTab]);

        // And finally save the field layout
        try {
            return (Craft::$app->fields->saveLayout($fieldLayout) && Craft::$app->sections->saveEntryType($pagesEntry));
        } catch (EntryTypeNotFoundException $e) {
            echo $e->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
        return true;
    }

}