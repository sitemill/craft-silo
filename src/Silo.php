<?php
/**
 * Digital asset manager plugin for Craft CMS 3.x
 *
 * @link      https://sitemill.co
 * @copyright Copyright (c) 2020 SiteMill
 */

namespace sitemill\silo;

use sitemill\silo\services\Lightbox as LightboxService;
use sitemill\silo\services\Download as DownloadService;
use sitemill\silo\services\SiloSettings as SiloSettingsService;
use sitemill\silo\services\Setup as SetupService;
use sitemill\silo\variables\SiloVariable;
use sitemill\silo\elements\SiloAsset;
use sitemill\silo\models\Settings;
use sitemill\silo\behaviours\CraftVariableBehavior;

use Craft;
use craft\helpers\UrlHelper;
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
 * @package   Silo
 * @since     1.0.0
 *
 * @property  LightboxService $lightbox
 * @property  SiloSettingsService $siloSettings
 * @property  DownloadService $download
 * @property  SetupService $setup
 */
class Silo extends Plugin
{


    // Static Properties
    // =========================================================================

    /**
     * @var Silo
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

        // Register project config
        Craft::$app->projectConfig
            ->onAdd('silo', [self::$plugin->siloSettings, 'handleUpdateFieldLayout'])
            ->onUpdate('silo', [self::$plugin->siloSettings, 'handleUpdateFieldLayout']);
//            ->onRemove('siloAssets', [self::$plugin->siloSettings, 'handleDeletedSiloSettings']);


        // Register routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['silo/assets/upload'] = 'silo/silo-assets/upload-silo-assets';
            }
        );

        // Register element type
        Event::on(Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SiloAsset::class;
            }
        );

        // Register field type
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SiloAssetsField::class;
            }
        );

        // Register permissions
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[Craft::t('silo', 'Asset Manager')] = [
                'silo-editSiloAssets' => [
                    'label' => Craft::t('silo', 'Edit Silo assets'), 'nested' => [
                        'silo-createSiloAssets' => ['label' => Craft::t('silo', 'Create assets')],
                        'silo-approveSiloAssets' => ['label' => Craft::t('silo', 'Approve assets')],
                        'silo-deleteSiloAssets' => ['label' => Craft::t('silo', 'Delete assets')],
                        'silo-editPeerSiloAssets' => [
                            'label' => Craft::t('silo', 'Edit other authors\' assets'), 'nested' => [
                                'silo-approvePeerSiloAssets' => ['label' => Craft::t('silo', 'Approve other authors\' assets')],
                                'silo-deletePeerSiloAssets' => ['label' => Craft::t('silo', 'Delete other authors\' assets')],
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
                $event->rules['silo-assets'] = ['template' => 'silo/silo-assets/index'];
                $event->rules['silo-assets/<siloAssetId:\d+><slug:(?:-{slug})?>'] = 'silo/silo-assets/edit-silo-asset';
                $event->rules['silo/settings'] = 'silo/settings/index';
            }
        );

        // Register nav items
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'silo-assets',
                    'label' => 'Asset Manager',
//                    'icon' => 'sitemill/silo/icon.svg',
                ];
            }
        );

        // Register Silo variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('silo', SiloVariable::class);
            }
        );

        // Register SiloAsset behaviour
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
                'silo',
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
    public function getSettingsResponse()
    {
        // Just redirect to the plugin settings page
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('silo/settings'));
    }

}
