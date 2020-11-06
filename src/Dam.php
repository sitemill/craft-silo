<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\dam;

use sitemill\dam\fields\DamAssetsField as DamAssetsField;
use sitemill\dam\services\Lightbox as LightboxService;
use sitemill\dam\services\Download as DownloadService;
use sitemill\dam\services\DamAssets as DamAssetsService;
use sitemill\dam\services\Setup as SetupService;
use sitemill\dam\variables\DamVariable;
use sitemill\dam\elements\DamAsset;
use sitemill\dam\models\Settings;
use sitemill\dam\behaviours\CraftVariableBehavior;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\services\Elements;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use craft\web\View;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\UserPermissions;

use yii\base\Event;

/**
 * Class Library
 *
 * @author    SiteMill
 * @package   Dam
 * @since     1.0.0
 *
 * @property  LightboxService $lightbox
 * @property  DamAssetsService $libraryAssets
 * @property  DownloadService $download
 * @property  SetupService $setup
 */
class Dam extends Plugin
{


    // Static Properties
    // =========================================================================

    /**
     * @var Dam
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;


        // Register routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['dam/assets/upload'] = 'dam/dam-assets/upload-dam-assets';
            }
        );

        // Register element type
        Event::on(Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = DamAsset::class;
            }
        );

        // Register field type
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = DamAssetsField::class;
            }
        );

        // Register permissions
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[Craft::t('dam', 'Asset Manager')] = [
                'dam-editDamAssets' => [
                    'label' => Craft::t('dam', 'Edit DAM assets'), 'nested' => [
                        'dam-createDamAssets' => ['label' => Craft::t('dam', 'Create assets')],
                        'dam-approveDamAssets' => ['label' => Craft::t('dam', 'Approve assets')],
                        'dam-deleteDamAssets' => ['label' => Craft::t('dam', 'Delete assets')],
                        'dam-editPeerDamAssets' => [
                            'label' => Craft::t('dam', 'Edit other authors\' assets'), 'nested' => [
                                'dam-approvePeerDamAssets' => ['label' => Craft::t('dam', 'Approve other authors\' assets')],
                                'dam-deletePeerDamAssets' => ['label' => Craft::t('dam', 'Delete other authors\' assets')],
                            ]
                        ],
                    ]
                ],
            ];
        });

        // Register CP Routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['dam-assets'] = ['template' => 'dam/dam-assets/index'];
                $event->rules['dam-assets/<damAssetId:\d+><slug:(?:-{slug})?>'] = 'dam/dam-assets/edit-dam-asset';
                $event->rules['settings/plugins/dam/dam-assets'] = 'dam/dam-assets/edit-field-layout';
            }
        );

        // Register nav items
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'dam-assets',
                    'label' => 'Asset Manager',
//                    'icon' => 'sitemill/dam/icon.svg',
                ];
            }
        );

        // Register DAM variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('dam', DamVariable::class);
            }
        );

        // Register DamAsset behaviour
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;

            // Attach a behavior:
            $variable->attachBehaviors([
                CraftVariableBehavior::class,
            ]);
        });

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'dam',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'dam/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

}
