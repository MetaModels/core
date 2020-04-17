<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Doctrine\DBAL\Connection;
use Symfony\Component\Finder\Finder;

/**
 * Handy helper class to retrieve a list of templates.
 */
class TemplateList
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The resource directories.
     *
     * @var string[]
     */
    private $resourceDirs;

    /**
     * The project root directory.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Create a new instance.
     *
     * @param Connection $database     The database connection.
     * @param string[]   $resourceDirs The resource directories.
     * @param string     $rootDir      The root directory.
     */
    public function __construct(Connection $database, $resourceDirs, $rootDir)
    {
        $this->database     = $database;
        $this->resourceDirs = $resourceDirs;
        $this->rootDir      = $rootDir;
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
            $this->fetchTemplatesFromResourceDirectories($templateBaseName)
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

    /**
     * Retrieve the message to use when not within a theme (aka global scope).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getNoThemeMessage()
    {
        return $GLOBALS['TL_LANG']['MSC']['no_theme'];
    }

    /**
     * Fetch the templates from TL_ROOT/templates/.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     */
    private function fetchRootTemplates($templateBaseName)
    {
        return $this->getTemplatesForBaseFrom(
            $templateBaseName,
            $this->rootDir . '/templates',
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
    private function fetchTemplatesFromThemes($templateBaseName)
    {
        $allTemplates = [];
        $themes       = $this
            ->database
            ->createQueryBuilder()
            ->select('t.id, t.name, sguiHbdjvLt.templates')
            ->from('tl_theme', 't')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        // Add all the theme templates folders.
        foreach ($themes as $theme) {
            $templateDir = $theme['templates'];
            $themeName   = $theme['name'];
            if ($templateDir != '') {
                $allTemplates = array_replace_recursive(
                    $allTemplates,
                    $this->getTemplatesForBaseFrom(
                        $templateBaseName,
                        $this->rootDir . '/' . $templateDir,
                        $themeName
                    )
                );
            }
        }

        return $allTemplates;
    }

    /**
     * Fetch the templates from resource locations.
     *
     * @param string $templateBaseName The base name for the templates to retrieve.
     *
     * @return array
     */
    private function fetchTemplatesFromResourceDirectories($templateBaseName)
    {
        $allTemplates = [];
        $themeName    = $this->getNoThemeMessage();
        // Add the module templates folders if they exist.
        foreach ($this->resourceDirs as $resourceDir) {
            $allTemplates = array_replace_recursive(
                $allTemplates,
                $this->getTemplatesForBaseFrom($templateBaseName, $resourceDir . '/templates', $themeName)
            );
        }

        return $allTemplates;
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
    private function getTemplatesForBaseFrom($base, $folder, $themeName)
    {
        if (!is_dir($folder)) {
            return [];
        }

        $themeName      = trim($themeName);
        $foundTemplates = Finder::create()->in($folder)->name($base . '*');

        $templates = [];
        foreach ($foundTemplates as $template) {
            /** @var \Symfony\Component\Finder\SplFileInfo $template */
            $templates[$template->getBasename('.' . $template->getExtension())] = [$themeName => $themeName];
        }

        return $templates;
    }
}
