<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use MetaModels\IMetaModelsServiceContainer;
use MetaModels\IServiceContainerAware;

/**
 * Handy helper class to retrieve a list of templates.
 */
class TemplateList implements IServiceContainerAware
{
    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Set the service container to use.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return TemplateList
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the service container in use.
     *
     * @return IMetaModelsServiceContainer|null
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the message to use when not within a theme (aka global scope).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getNoThemeMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['no_theme'];
    }

    /**
     * Fetch a list of matching templates of the current base within the given folder and the passed theme name.
     *
     * @param string $base      The base for the templates to be retrieved.
     *
     * @param string $folder    The folder to search in.
     *
     * @param string $themeName The name of the theme for the given folder (will get used in the returned description
     *                          text).
     *
     * @return array
     */
    protected function getTemplatesForBaseFrom($base, $folder, $themeName)
    {
        $themeName      = trim($themeName);
        $themeTemplates = glob($folder . '/' . $base . '*');

        if (!$themeTemplates) {
            return array();
        }

        $templates = array();

        foreach ($themeTemplates as $template) {
            $template = basename($template, strrchr($template, '.'));

            $templates[$template] = array($themeName => $themeName);
        }

        return $templates;
    }

    /**
     * Fetch the templates from TL_ROOT/templates/.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     */
    protected function fetchRootTemplates($templateBaseName)
    {
        return $this->getTemplatesForBaseFrom(
            $templateBaseName,
            TL_ROOT . '/templates',
            $this->getNoThemeMessage()
        );
    }

    /**
     * Fetch the templates from TL_ROOT/templates/.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     */
    protected function fetchTemplatesFromThemes($templateBaseName)
    {
        $allTemplates = array();
        $themes       = $this
            ->getServiceContainer()
            ->getDatabase()
            ->prepare('SELECT id,name,templates FROM tl_theme')
            ->execute();

        // Add all the theme templates folders.
        while ($themes->next()) {
            /** @noinspection PhpUndefinedFieldInspection  */
            $templateDir = $themes->templates;
            /** @noinspection PhpUndefinedFieldInspection  */
            $themeName = $themes->name;
            if ($templateDir != '') {
                $allTemplates = array_replace_recursive(
                    $allTemplates,
                    $this->getTemplatesForBaseFrom(
                        $templateBaseName,
                        TL_ROOT . '/' . $templateDir,
                        $themeName
                    )
                );
            }
        }

        return $allTemplates;
    }

    /**
     * Fetch the templates from TL_ROOT/templates/.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     */
    protected function fetchTemplatesFromModules($templateBaseName)
    {
        $allTemplates = array();

        // Add the module templates folders if they exist.
        foreach (\Config::getInstance()->getActiveModules() as $strModule) {
            $allTemplates = array_replace_recursive(
                $allTemplates,
                self::getTemplatesForBaseFrom(
                    $templateBaseName,
                    TL_ROOT . '/system/modules/' . $strModule . '/templates',
                    $this->getNoThemeMessage()
                )
            );
        }

        return $allTemplates;
    }

    /**
     * Fetch the template group for the detail view of the current MetaModel module.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getTemplatesForBase($templateBaseName)
    {
        $allTemplates = array_replace_recursive(
            $this->fetchTemplatesFromThemes($templateBaseName),
            $this->fetchRootTemplates($templateBaseName),
            $this->fetchTemplatesFromModules($templateBaseName)
        );

        $templateList = array();
        foreach ($allTemplates as $template => $themeList) {
            $templateList[$template] = sprintf(
                $GLOBALS['TL_LANG']['MSC']['template_in_theme'],
                $template,
                implode(', ', $themeList)
            );
        }

        ksort($templateList);

        return array_unique($templateList);
    }
}
