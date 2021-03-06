<?php

namespace Bolt\Menu;

use Bolt\Translation\Translator as Trans;
use Silex\Application;

/**
 * Bolt admin (back-end) area menu builder.
 *
 * @internal Backwards compatibility not guaranteed on this class presently.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class AdminMenuBuilder
{
    /** @var MenuEntry */
    protected $rootEntry;
    /** @var MenuEntry[] */
    protected $children;

    /**
     * Constructor.
     *
     * @param MenuEntry $rootEntry
     */
    public function __construct(MenuEntry $rootEntry)
    {
        $this->rootEntry = $rootEntry;
    }

    /**
     * Build the menus.
     *
     * @param Application $app
     *
     * @return \Bolt\Menu\MenuEntry
     */
    public function build(Application $app)
    {
        $this->addConfiguration($app);
        $this->addFileManagement($app);
        $this->addTranslations($app);
        $this->addExtend();

        return $this->rootEntry;
    }

    /**
     * Configuration menus.
     *
     * @param Application $app
     */
    protected function addConfiguration(Application $app)
    {
        // Main configuration
        $configEntry = $this->rootEntry->add(
            (new MenuEntry('config', 'config'))
                ->setLabel(Trans::__('Configuration'))
                ->setIcon('fa:cogs')
                ->setPermission('settings')
        );

        // Users & Permssions
        $path = $this->rootEntry->getUri() . '/users';
        $configEntry->add(
            (new MenuEntry('users', $path))
                ->setLabel(Trans::__('Users & Permissions'))
                ->setIcon('fa:group')
                ->setPermission('users')
        );

        // Main configuration
        $path = $app['url_generator']->generate('fileedit', ['namespace' => 'config', 'file' => 'config.yml']);
        $configEntry->add(
            (new MenuEntry('config_main', $path))
                ->setLabel(Trans::__('Main configuration'))
                ->setIcon('fa:cog')
                ->setPermission('files:config')
        );

        // ContentTypes
        $path = $app['url_generator']->generate('fileedit', ['namespace' => 'config', 'file' => 'contenttypes.yml']);
        $configEntry->add(
            (new MenuEntry('config_contenttypes', $path))
                ->setLabel(Trans::__('Contenttypes'))
                ->setIcon('fa:paint-brush')
                ->setPermission('files:config')
        );

        // Taxonomy
        $path = $app['url_generator']->generate('fileedit', ['namespace' => 'config', 'file' => 'taxonomy.yml']);
        $configEntry->add(
            (new MenuEntry('config_taxonomy', $path))
                ->setLabel(Trans::__('Taxonomy'))
                ->setIcon('fa:tags')
                ->setPermission('files:config')
        );

        // Menus
        $path = $app['url_generator']->generate('fileedit', ['namespace' => 'config', 'file' => 'menu.yml']);
        $configEntry->add(
            (new MenuEntry('config_menu', $path))
            ->setLabel(Trans::__('Menu setup'))
            ->setIcon('fa:list')
            ->setPermission('files:config')
        );

        // Routing
        $path = $app['url_generator']->generate('fileedit', ['namespace' => 'config', 'file' => 'routing.yml']);
        $configEntry->add(
            (new MenuEntry('config_routing', $path))
                ->setLabel(Trans::__('menu.configuration.routing'))
                ->setIcon('fa:random')
                ->setPermission('files:config')
        );

        // Database checks
        $path = $this->rootEntry->getUri() . '/dbcheck';
        $configEntry->add(
            (new MenuEntry('dbcheck', $path))
                ->setLabel(Trans::__('Check database'))
                ->setIcon('fa:database')
                ->setPermission('dbupdate')
        );

        // Cache flush
        $path = $this->rootEntry->getUri() . '/clearcache';
        $configEntry->add(
            (new MenuEntry('clearcache', $path))
                ->setLabel(Trans::__('Clear the cache'))
                ->setIcon('fa:eraser')
                ->setPermission('clearcache')
        );

        // Change log
        $path = $this->rootEntry->getUri() . '/changelog';
        $configEntry->add(
            (new MenuEntry('log_change', $path))
                ->setLabel(Trans::__('logs.change-log'))
                ->setIcon('fa:archive')
                ->setPermission('changelog')
        );

        // System log
        $path = $this->rootEntry->getUri() . '/systemlog';
        $configEntry->add(
            (new MenuEntry('log_system', $path))
            ->setLabel(Trans::__('logs.system-log'))
            ->setIcon('fa:archive')
            ->setPermission('systemlog')
        );
    }

    /**
     * File management menus.
     *
     * @param Application $app
     */
    protected function addFileManagement(Application $app)
    {
        $fileEntry = $this->rootEntry->add(
            (new MenuEntry('files', 'files'))
                ->setLabel(Trans::__('Extensions'))
                ->setIcon('fa:cubes')
                ->setPermission('extensions')
        );

        // Uploaded files
        $path = $app['url_generator']->generate('files', ['namespace' => 'files', 'path' => '']);
        $fileEntry->add(
            (new MenuEntry('files_uploads', $path))
                ->setLabel(Trans::__('Uploaded files'))
                ->setIcon('fa:folder-open-o')
                ->setPermission('files:uploads')
        );

        // Themes
        $path = $app['url_generator']->generate('files', ['namespace' => 'themes', 'path' => '']);
        $fileEntry->add(
            (new MenuEntry('files_themes', $path))
                ->setLabel(Trans::__('View/edit Templates'))
                ->setIcon('fa:desktop')
                ->setPermission('files:theme')
        );
    }

    /**
     * Translations menus.
     *
     * @param Application $app
     */
    protected function addTranslations(Application $app)
    {
        $translationEntry = $this->rootEntry->add(
            (new MenuEntry('translations', 'tr'))
                ->setLabel(Trans::__('Translations'))
                ->setPermission('translation')
        );

        // Messages
        $path = $app['url_generator']->generate('translation', ['domain' => 'messages']);
        $translationEntry->add(
            (new MenuEntry('tr_messages', $path))
                ->setLabel(Trans::__('Messages'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
        );

        // Long messages
        $path = $app['url_generator']->generate('translation', ['domain' => 'infos']);
        $translationEntry->add(
            (new MenuEntry('tr_long_messages', $path))
                ->setLabel(Trans::__('Long messages'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
        );

        // Contenttypes
        $path = $app['url_generator']->generate('translation', ['domain' => 'contenttypes']);
        $translationEntry->add(
            (new MenuEntry('tr_contenttypes', $path))
                ->setLabel(Trans::__('Contenttypes'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
        );
    }

    /**
     * Extend menus.
     */
    protected function addExtend()
    {
        $this->rootEntry->add(
            (new MenuEntry('extend', 'extend'))
                ->setLabel(Trans::__('Extensions'))
                ->setIcon('fa:cubes')
                ->setPermission('extensions')
        );
    }
}
