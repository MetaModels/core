<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render;

use Contao\Config;
use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\Adapter;
use Contao\FrontendTemplate;
use Contao\System;
use Contao\TemplateLoader;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Exception;
use MetaModels\Helper\ContaoController;
use RuntimeException;

use function array_key_exists;

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
     * @var Adapter|Adapter<TemplateLoader> Template loader adapter.
     */
    protected $templateLoader;

    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    protected $scopeDeterminator;

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
     * @param Adapter|Adapter<TemplateLoader>|null $templateLoader    Template loader adapter.
     * @param RequestScopeDeterminator|null        $scopeDeterminator Request scope determinator.
     */
    public function __construct(
        $strTemplate = '',
        Adapter $templateLoader = null,
        RequestScopeDeterminator $scopeDeterminator = null
    ) {
        $this->strTemplate = $strTemplate;

        if (null === $templateLoader) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the template loader as 2nd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $templateLoader = System::getContainer()->get('contao.framework')?->getAdapter(TemplateLoader::class);
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
            return self::$templatePathCache[$strTemplate][$strFormat][$strCustom] !== false
                ? self::$templatePathCache[$strTemplate][$strFormat][$strCustom]
                : null;
        }

        try {
            /** @psalm-suppress InternalMethod - the ContaoFramework class is internal, not the method usage. */
            self::$templatePathCache[$strTemplate][$strFormat][$strCustom] = $this->templateLoader->getPath(
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

        // Add start and end markers in debug mode.
        $container = System::getContainer();
        if ($container && $container->getParameter('kernel.debug') && ('html5' === $this->strFormat)) {
            $rootDir = $container->getParameter('kernel.project_dir');
            assert(\is_string($rootDir));
            $strRelPath =
                \str_replace($rootDir . '/', '', (string) $this->getTemplate($this->strTemplate, $this->strFormat));
            $strBuffer  = <<<EOF
<!-- TEMPLATE START: $strRelPath -->
$strBuffer
<!-- TEMPLATE END: $strRelPath -->

EOF;
        }

        return (string) $strBuffer;
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
    public function insert($strName, array $arrData = null)
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
