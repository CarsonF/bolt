<?php

namespace Bolt\Menu;

use Bolt\Translation\Translator as Trans;

/**
 * Bolt admin (back-end) area menu builder.
 *
 * @internal Backwards compatibility not guaranteed on this class presently.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
final class AdminMenuBuilder
{
    /**
     * Build the menus.
     *
     * @param MenuEntry $root
     *
     * @return MenuEntry
     */
    public function build(MenuEntry $root)
    {
        $this->addConfiguration($root);
        $this->addFileManagement($root);
        $this->addTranslations($root);
        $this->addExtend($root);

        return $root;
    }

    /**
     * Configuration menus.
     *
     * @param MenuEntry $root
     */
    protected function addConfiguration(MenuEntry $root)
    {
        // Main configuration
        $root->add('config', 'config')
            ->setLabel(Trans::__('general.phrase.configuration'))
            ->setIcon('fa:cogs')
            ->setPermission('settings')
            ->add('users')
                ->setRoute('users')
                ->setLabel(Trans::__('general.phrase.users-permissions'))
                ->setIcon('fa:group')
                ->setPermission('users')
            ->up()
            ->add('config_main')
                ->setRoute('fileedit', ['namespace' => 'config', 'file' => 'config.yml'])
                ->setLabel(Trans::__('general.phrase.configuration-main'))
                ->setIcon('fa:cog')
                ->setPermission('files:config')
            ->up()
            ->add('config_contenttypes')
                ->setRoute('fileedit', ['namespace' => 'config', 'file' => 'contenttypes.yml'])
                ->setLabel(Trans::__('general.phrase.content-types'))
                ->setIcon('fa:paint-brush')
                ->setPermission('files:config')
            ->up()
            ->add('config_taxonomy')
                ->setRoute('fileedit', ['namespace' => 'config', 'file' => 'taxonomy.yml'])
                ->setLabel(Trans::__('general.phrase.taxonomy'))
                ->setIcon('fa:tags')
                ->setPermission('files:config')
            ->up()
            ->add('config_menu')
                ->setRoute('fileedit', ['namespace' => 'config', 'file' => 'menu.yml'])
                ->setLabel(Trans::__('general.phrase.menu-setup'))
                ->setIcon('fa:list')
                ->setPermission('files:config')
            ->up()
            ->add('config_routing')
                ->setRoute('fileedit', ['namespace' => 'config', 'file' => 'routing.yml'])
                ->setLabel(Trans::__('menu.configuration.routing'))
                ->setIcon('fa:random')
                ->setPermission('files:config')
            ->up()
            ->add('dbcheck')
                ->setRoute('dbcheck')
                ->setLabel(Trans::__('general.phrase.check-database'))
                ->setIcon('fa:database')
                ->setPermission('dbupdate')
            ->up()
            ->add('clearcache')
                ->setRoute('clearcache')
                ->setLabel(Trans::__('general.phrase.clear-cache'))
                ->setIcon('fa:eraser')
                ->setPermission('clearcache')
            ->up()
            ->add('log_change')
                ->setRoute('changelog')
                ->setLabel(Trans::__('logs.change-log'))
                ->setIcon('fa:archive')
                ->setPermission('changelog')
            ->up()
            ->add('log_system')
                ->setRoute('systemlog')
                ->setLabel(Trans::__('logs.system-log'))
                ->setIcon('fa:archive')
                ->setPermission('systemlog')
            ->up()
        ;
    }

    /**
     * File management menus.
     *
     * @param MenuEntry $root
     */
    protected function addFileManagement(MenuEntry $root)
    {
        $root->add('files', 'files')
            ->setLabel(Trans::__('general.phrase.extensions'))
            ->setIcon('fa:cubes')
            ->setPermission('extensions')
            ->add('files_uploads')
                ->setRoute('files')
                ->setLabel(Trans::__('general.phrase.general.phrase.uploaded-files'))
                ->setIcon('fa:folder-open-o')
                ->setPermission('files:uploads')
            ->up()
            ->add('files_themes')
                ->setRoute('files', ['namespace' => 'themes'])
                ->setLabel(Trans::__('general.phrase.view-edit-templates'))
                ->setIcon('fa:desktop')
                ->setPermission('files:theme')
            ->up()
        ;
    }

    /**
     * Translations menus.
     *
     * @param MenuEntry $root
     */
    protected function addTranslations(MenuEntry $root)
    {
        $root->add('translations', 'tr')
            ->setLabel(Trans::__('general.phrase.translations'))
            ->setPermission('translation')
            ->add('tr_messages')
                ->setRoute('translation', ['domain' => 'messages'])
                ->setLabel(Trans::__('general.phrase.messages'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
            ->up()
            ->add('tr_long_messages')
                ->setRoute('translation', ['domain' => 'infos'])
                ->setLabel(Trans::__('general.phrase.long-messages'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
            ->up()
            ->add('tr_contenttypes')
                ->setRoute('translation', ['domain' => 'contenttypes'])
                ->setLabel(Trans::__('general.phrase.content-types'))
                ->setIcon('fa:flag')
                ->setPermission('translation')
            ->up()
        ;
    }

    /**
     * Extend menus.
     *
     * @param MenuEntry $root
     */
    protected function addExtend(MenuEntry $root)
    {
        $root->add('extend', 'extend')
            ->setLabel(Trans::__('Extend'))
            ->setIcon('fa:cubes')
            ->setPermission('extensions')
        ;
    }
}
