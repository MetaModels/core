<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render;

use Contao\BackendTemplate;
use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\CoreBundle\Framework\Adapter;
use Contao\FrontendTemplate;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Exception;
use InvalidArgumentException;
use MetaModels\Helper\ContaoController;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Template class for MetaModels.
 * In most aspects this behaves identically to the FrontendTemplate class from Contao, but it differs in respect to
 * format selection.
 * The format is being determined upon parsing and not upon instantiation. There is also an optional "fail on not
 * found" flag, which defaults to false and therefore one can parse the template and have zero output instead of
 * cluttering the frontend with exceptions.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Template
{
    /**
     * Template file.
     *
     * @var string
     */
    protected $strTemplate;

    /**
     * Parent template.
     *
     * @var string|null
     */
    protected $strParent;

    /**
     * Default template.
     *
     * @var string|null
     */
    protected $strDefault;

    /**
     * Template data.
     *
     * @var array
     */
    protected $arrData = [];

    /**
     * Current output format. Only valid when within {@link MetaModelTemplate::parse()}.
     *
     * @var string
     */
    protected $strFormat = '';

    /**
     * Blocks.
     *
     * @var array
     */
    protected $arrBlocks = [];

    /**
     * Block names.
     *
     * @var array
     */
    protected $arrBlockNames = [];

    /**
     * The template loader.
     *
     * Contao 6 removed Contao\TemplateLoader entirely - this property is never actually read
     * (resolveTemplatePath() reimplements the lookup itself, see below), so the type is kept as a
     * bare Adapter rather than referencing a class that no longer exists.
     *
     * @var Adapter Template loader adapter.
     */
    protected $templateLoader;

    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    protected $scopeDeterminator;

    /**
     * The Twig surrogate. When set, a matching Twig template takes precedence over the legacy PHP template.
     *
     * @var TwigTemplateSurrogate|null
     */
    protected $twigSurrogate;

    /**
     * The Twig template group ("attribute", "filter" or "item") used to build the Twig candidate name
     * "@Contao/metamodels/&lt;group&gt;/&lt;leaf&gt;.html.twig". Empty disables the Twig surrogate.
     *
     * @var string
     */
    protected $twigGroup = '';

    /**
     * The kernel project directory, used to detect legacy template overrides in the project "templates/" folder.
     *
     * @var string
     */
    protected $projectDir = '';

    /**
     * Template path cache.
     *
     * Storing state of template path detection in a cache array for each template format and custom location.
     *
     * @var array<string, array<string, array<string, string|false>>>
     */
    protected static $templatePathCache = [];

    /**
     * Makes all protected methods from class Controller callable publicly.
     *
     * @param string $strMethod The method name.
     * @param array  $arrArgs   The parameters for the method.
     *
     * @return mixed
     */
    public function __call($strMethod, $arrArgs)
    {
        if (isset($this->$strMethod) && \is_callable($this->$strMethod)) {
            return \call_user_func_array($this->$strMethod, $arrArgs);
        }

        /** @psalm-suppress DeprecatedClass */
        return \call_user_func_array(array(ContaoController::getInstance(), $strMethod), $arrArgs);
    }

    /**
     * Create a new template instance.
     *
     * @param string                               $strTemplate       The name of the template file.
     * @param Adapter|null                          $templateLoader    Template loader adapter.
     * @param RequestScopeDeterminator|null        $scopeDeterminator Request scope determinator.
     * @param TwigTemplateSurrogate|null           $twigSurrogate     Twig surrogate (enables Twig precedence).
     * @param string                               $twigGroup         Twig template group (attribute|filter|item).
     * @param string                               $projectDir        The kernel project directory.
     */
    public function __construct(
        $strTemplate = '',
        ?Adapter $templateLoader = null,
        ?RequestScopeDeterminator $scopeDeterminator = null,
        ?TwigTemplateSurrogate $twigSurrogate = null,
        string $twigGroup = '',
        string $projectDir = ''
    ) {
        $this->strTemplate   = $strTemplate;
        $this->twigSurrogate = $twigSurrogate;
        $this->twigGroup     = $twigGroup;
        $this->projectDir    = $projectDir;

        if (null === $templateLoader) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the template loader as 2nd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            // Not a class-constant reference: Contao\TemplateLoader no longer exists under Contao 6,
            // and this adapter is only ever a write-only property (see the class docblock above)
            // that's never actually invoked, so there's nothing left to autoload here regardless.
            $templateLoader = System::getContainer()->get('contao.framework')->getAdapter('Contao\TemplateLoader');
            assert($templateLoader instanceof Adapter);
        }
        $this->templateLoader = $templateLoader;

        if (null === $scopeDeterminator) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the request scope determinator as 3rd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $scopeDeterminator = System::getContainer()->get('cca.dc-general.scope-matcher');
            assert($scopeDeterminator instanceof RequestScopeDeterminator);
        }
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Set an object property.
     *
     * @param string $strKey   The name of the property.
     * @param mixed  $varValue The value to set.
     *
     * @return void
     */
    public function __set($strKey, $varValue)
    {
        $this->arrData[$strKey] = $varValue;
    }

    /**
     * Return an object property.
     *
     * @param string $strKey The name of the property.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function __get($strKey)
    {
        if (\array_key_exists($strKey, $this->arrData)) {
            return $this->arrData[$strKey];
        }

        if (!empty($GLOBALS['TL_CONFIG']['debugMode'])) {
            \trigger_error($this->getName() . ': Undefined template variable: ' . $strKey, E_USER_WARNING);
        }
        return null;
    }

    /**
     * Check whether a property is set.
     *
     * @param string $strKey The name of the property.
     *
     * @return boolean
     */
    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }

    /**
     * Set the template data from an array.
     *
     * @param array $arrData The properties to be set.
     *
     * @return void
     */
    public function setData($arrData)
    {
        $this->arrData = $arrData;
    }

    /**
     * Return the template data as array.
     *
     * @return array
     */
    public function getData()
    {
        return $this->arrData;
    }

    /**
     * Set the template name.
     *
     * @param string $strTemplate The new name.
     *
     * @return void
     */
    public function setName($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * Return the template name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->strTemplate;
    }

    /**
     * Print all template variables to the screen using print_r.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
     */
    public function showTemplateVars()
    {
        echo "<pre>\n";
        // @codingStandardsIgnoreStart - We really want to keep this debug function here.
        \print_r($this->arrData);
        // @codingStandardsIgnoreEnd
        echo "</pre>\n";
    }

    /**
     * Print all template variables to the screen using var_dump.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
     *
     * @psalm-suppress ForbiddenCode
     */
    public function dumpTemplateVars()
    {
        echo "<pre>\n";
        // @codingStandardsIgnoreStart - We really want to keep this debug function here.
        \var_dump($this->arrData);
        // @codingStandardsIgnoreEnd
        echo "</pre>\n";
    }

    /**
     * Find a particular template file and return its path.
     *
     * @param string $strTemplate       Name of the template file.
     * @param string $strFormat         The format to search for.
     * @param bool   $blnFailIfNotFound Boolean flag telling if an Exception shall be thrown when the file can not
     *                                  be found.
     *
     * @throws RuntimeException When the flag has been set and the file has not been found.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getTemplate($strTemplate, $strFormat = 'html5', $blnFailIfNotFound = false)
    {
        $strTemplate = \basename($strTemplate);
        $strCustom   = 'templates';

        // Check for a theme folder if scope frontend and a normal page.
        if (isset($GLOBALS['objPage']) && $this->scopeDeterminator->currentScopeIsFrontend()) {
            $tmpDir = \str_replace('../', '', (string) $GLOBALS['objPage']->templateGroup);
            if ('' !== $tmpDir) {
                $strCustom = $tmpDir;
            }
        }

        if (
            isset(self::$templatePathCache[$strTemplate][$strFormat])
            && \array_key_exists($strCustom, self::$templatePathCache[$strTemplate][$strFormat])
        ) {
            $value = self::$templatePathCache[$strTemplate][$strFormat][$strCustom] !== false
                ? self::$templatePathCache[$strTemplate][$strFormat][$strCustom]
                : null;
            if ($blnFailIfNotFound && null === $value) {
                throw new \Exception('Could not find template "' . $strTemplate . '"');
            }

            return $value;
        }

        try {
            self::$templatePathCache[$strTemplate][$strFormat][$strCustom] = $this->resolveTemplatePath(
                $strTemplate,
                $strFormat,
                $strCustom
            );

            return self::$templatePathCache[$strTemplate][$strFormat][$strCustom];
        } catch (Exception $exception) {
            self::$templatePathCache[$strTemplate][$strFormat][$strCustom] = false;
            if ($blnFailIfNotFound) {
                throw new RuntimeException(
                    \sprintf('Could not find template %s.%s', $strTemplate, $strFormat),
                    1,
                    $exception
                );
            }
        }

        return null;
    }

    /**
     * Find a template file's path, mirroring the algorithm the (Contao 6 removed) TemplateLoader::getPath() used:
     * the custom (theme) folder first, then the project's global "templates" folder, then every installed bundle's
     * "Resources/contao/templates" folder (last match wins, matching Contao's bundle load order/override rules).
     *
     * @param string $strTemplate The template name.
     * @param string $strFormat   The output format (e.g. "html5").
     * @param string $strCustom   The custom templates folder (defaults to "templates").
     *
     * @throws Exception When the template file can not be found.
     *
     * @return string
     */
    private function resolveTemplatePath(string $strTemplate, string $strFormat, string $strCustom): string
    {
        $file = $strTemplate . '.' . $strFormat;

        if (\file_exists($this->projectDir . '/' . $strCustom . '/' . $file)) {
            return $this->projectDir . '/' . $strCustom . '/' . $file;
        }

        if ('templates' !== $strCustom && \file_exists($this->projectDir . '/templates/' . $file)) {
            return $this->projectDir . '/templates/' . $file;
        }

        $resourceFinder = System::getContainer()->get('contao.resource_finder');
        \assert($resourceFinder instanceof ResourceFinderInterface);

        $strPath = null;
        try {
            foreach ($resourceFinder->findIn('templates')->name($file) as $found) {
                $strPath = $found->getPathname();
            }
        } catch (InvalidArgumentException $exception) {
            // No bundle ships a "templates" resource directory at all - fall through to the not-found exception.
            unset($exception);
        }

        if (null !== $strPath) {
            return $strPath;
        }

        throw new Exception('Could not find template "' . $strTemplate . '"');
    }

    /**
     * Call the parse Template HOOK.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function callParseTemplateHook()
    {
        if (
            isset($GLOBALS['METAMODEL_HOOKS']['parseTemplate'])
            && \is_array($GLOBALS['METAMODEL_HOOKS']['parseTemplate'])
        ) {
            foreach ($GLOBALS['METAMODEL_HOOKS']['parseTemplate'] as $callback) {
                [$strClass, $strMethod] = $callback;

                $objCallback = (\in_array('getInstance', \get_class_methods($strClass)))
                    ? \call_user_func(array($strClass, 'getInstance'))
                    : new $strClass();

                $objCallback->$strMethod($this);
            }
        }
    }

    /**
     * Parse the template file and return it as string.
     *
     * @param string  $strOutputFormat   The desired output format.
     * @param boolean $blnFailIfNotFound If set to true, the template object will throw an exception if the template
     *                                   can not be found. Defaults to false.
     *
     * @return string The parsed template.
     */
    public function parse($strOutputFormat, $blnFailIfNotFound = false)
    {
        if ($this->strTemplate === '') {
            return '';
        }

        // Set the format.
        $this->strFormat = $strOutputFormat;

        // HOOK: add custom parse filters.
        $this->callParseTemplateHook();

        // A matching Twig template takes precedence over the legacy PHP template (like Contao core does).
        if (null !== ($twigResult = $this->renderTwigSurrogate())) {
            return $twigResult;
        }

        $strBuffer = '';

        // Start with the template itself.
        $this->strParent = $this->strTemplate;

        // Include the parent templates.
        while ($this->strParent !== null) {
            $strCurrent = $this->strParent;
            $strParent  = $this->strDefault
                ?? $this->getTemplate($this->strParent, $this->strFormat, $blnFailIfNotFound);

            // Check if we have the template.
            if (null === $strParent) {
                return \sprintf(
                    'Template %s.%s not found (it is maybe within a unreachable theme folder?).',
                    $this->strParent,
                    $this->strFormat
                );
            }

            // Reset the flags.
            $this->strParent  = null;
            $this->strDefault = null;

            \ob_start();
            assert(\is_file($strParent));
            /** @var string|null $this->strParent */
            include($strParent);

            // Capture the output of the root template.
            if ($this->strParent === null) {
                $strBuffer = \ob_get_contents();
            } elseif ($this->strParent === $strCurrent) {
                $this->strDefault = $this->getTemplate($this->strParent, $this->strFormat, $blnFailIfNotFound);
            }

            \ob_end_clean();
        }

        // Reset the internal arrays.
        $this->arrBlocks = [];

        return $this->addDebugMarkers((string) $strBuffer);
    }

    /**
     * Render a matching Twig template, taking precedence over the legacy PHP template, or null to fall back.
     *
     * @return string|null The rendered markup or null when no Twig surrogate applies.
     */
    protected function renderTwigSurrogate(): ?string
    {
        if (null === $this->twigSurrogate) {
            return null;
        }

        // Transitional (deprecated, to be removed in 3.0 together with the ".html5" templates): a legacy override of
        // the flat template name in the project "templates/" directory (or a theme folder) still wins over a bundle
        // provided Twig template, so existing customisations keep working until they are migrated to
        // "templates/metamodels/<group>/<leaf>".
        if ($this->hasLegacyTemplateOverride()) {
            return null;
        }

        return $this->twigSurrogate->render(
            $this->strTemplate,
            $this->twigGroup,
            $this->strFormat,
            $this->arrData
        );
    }

    /**
     * Check whether the flat template name is overridden in the project template directory (or a theme folder).
     *
     * Such a legacy override keeps precedence over a bundle provided Twig template for backwards compatibility.
     *
     * @return bool
     *
     * @deprecated Deprecated since 2.5 and to be removed in 3.0 - migrate overrides to "templates/metamodels/...".
     */
    private function hasLegacyTemplateOverride(): bool
    {
        if (!\in_array($this->strFormat, ['html5', 'text'], true)) {
            return false;
        }

        if ('' === $this->projectDir) {
            return false;
        }

        $path = $this->getTemplate($this->strTemplate, $this->strFormat);
        if (null === $path) {
            return false;
        }

        $templatesDir = (\realpath($this->projectDir . '/templates') ?: ($this->projectDir . '/templates'))
            . \DIRECTORY_SEPARATOR;

        return \str_starts_with((string) (\realpath($path) ?: $path), $templatesDir);
    }

    /**
     * Wrap the buffer with template start/end markers when running in debug mode (html5 format only).
     *
     * @param string $strBuffer The rendered buffer.
     *
     * @return string The buffer, optionally wrapped with debug markers.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function addDebugMarkers(string $strBuffer)
    {
        $container = System::getContainer();
        if (
            !($container instanceof ContainerInterface)
            || true !== $container->getParameter('kernel.debug')
            || ('html5' !== $this->strFormat)
        ) {
            return $strBuffer;
        }

        $rootDir = $container->getParameter('kernel.project_dir');
        assert(\is_string($rootDir));
        $strRelPath =
            \str_replace($rootDir . '/', '', (string) $this->getTemplate($this->strTemplate, $this->strFormat));

        return <<<EOF
<!-- TEMPLATE START: $strRelPath -->
$strBuffer
<!-- TEMPLATE END: $strRelPath -->

EOF;
    }

    /**
     * Protected as only the included template file shall be able to call.
     *
     * This is needed to remain protected, as outside from {@link Template::parse()} the format is undefined.
     *
     * @return string
     */
    protected function getFormat()
    {
        return $this->strFormat;
    }

    /**
     * Static convenience method to perform the whole rendering within one line of code.
     *
     * @param string $strTemplate       Name of the template file.
     * @param string $strOutputFormat   The desired output format.
     * @param array  $arrTplData        The data to use in the template.
     *
     * @param bool   $blnFailIfNotFound If set to true, the template object will throw an exception if the template
     *                                  can not be found. Defaults to false.
     *
     * @return string
     */
    public static function render($strTemplate, $strOutputFormat, $arrTplData, $blnFailIfNotFound = false)
    {
        $objTemplate = new self($strTemplate);
        $objTemplate->setData($arrTplData);

        return $objTemplate->parse($strOutputFormat, $blnFailIfNotFound);
    }

    /**
     * Extend another template
     *
     * @param string $strName The template name.
     *
     * @return void
     */
    public function extend($strName)
    {
        $this->strParent = $strName;
    }

    /**
     * Insert the content of the parent block
     *
     * @return void
     */
    public function parent()
    {
        echo '[[TL_PARENT]]';
    }

    /**
     * Start a new block
     *
     * @param string $strName The block name.
     *
     * @return void
     *
     * @throws Exception If a child templates contains nested blocks.
     */
    public function block($strName)
    {
        $this->arrBlockNames[] = $strName;

        // Root template.
        if ($this->strParent === null) {
            // Register the block name.
            if (!isset($this->arrBlocks[$strName])) {
                $this->arrBlocks[$strName] = '[[TL_PARENT]]';
            } elseif (\is_array($this->arrBlocks[$strName])) {
                // Combine the contents of the child blocks
                $callback = static function (string $current, string $parent): string {
                    return \str_replace('[[TL_PARENT]]', $parent, $current);
                };

                $this->arrBlocks[$strName] = \array_reduce($this->arrBlocks[$strName], $callback, '[[TL_PARENT]]');
            }

            // Handle nested blocks.
            if ($this->arrBlocks[$strName] !== '[[TL_PARENT]]') {
                // Output everything before the first TL_PARENT tag.
                if (\strpos($this->arrBlocks[$strName], '[[TL_PARENT]]') !== false) {
                    [$content] = \explode('[[TL_PARENT]]', $this->arrBlocks[$strName], 2);
                    echo $content;
                } else {
                    // Output the current block and start a new output buffer to remove the following blocks
                    echo $this->arrBlocks[$strName];
                    \ob_start();
                }
            }
        } else {
            // Child template
            // Clean the output buffer.
            \ob_end_clean();

            // Check for nested blocks.
            if (\count($this->arrBlockNames) > 1) {
                throw new Exception('Nested blocks are not allowed in child templates');
            }

            // Start a new output buffer.
            \ob_start();
        }
    }

    /**
     * End a block
     *
     * @return void
     *
     * @throws Exception If there is no open block.
     */
    public function endblock()
    {
        // Check for open blocks.
        if (empty($this->arrBlockNames)) {
            throw new Exception('You must start a block before you can end it');
        }

        // Get the block name
        $name = \array_pop($this->arrBlockNames);

        // Root template.
        if ($this->strParent === null) {
            // Handle nested blocks
            if ($this->arrBlocks[$name] !== '[[TL_PARENT]]') {
                // Output everything after the first TL_PARENT tag
                if (\str_contains($this->arrBlocks[$name], '[[TL_PARENT]]')) {
                    [, $content] = \array_merge(\explode('[[TL_PARENT]]', $this->arrBlocks[$name], 2), ['']);
                    echo $content;
                } else {
                    // Remove the overwritten content
                    \ob_end_clean();
                }
            }
        } else {
            // Child template
            // Capture the block content.
            $this->arrBlocks[$name][] = \ob_get_clean();

            // Start a new output buffer
            \ob_start();
        }
    }

    /**
     * Insert a template
     *
     * @param string     $strName The template name.
     * @param array|null $arrData An optional data array.
     *
     * @return void
     */
    public function insert($strName, ?array $arrData = null)
    {
        if ($this->scopeDeterminator->currentScopeIsBackend()) {
            $objTemplate = new BackendTemplate($strName);
        } else {
            $objTemplate = new FrontendTemplate($strName);
        }

        if ($arrData !== null) {
            $objTemplate->setData($arrData);
        }

        echo $objTemplate->parse();
    }
}
