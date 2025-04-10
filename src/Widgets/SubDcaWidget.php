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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Danilo Benevides <danilobenevides01@gmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

use function class_exists;
use function count;
use function implode;
use function is_a;
use function is_array;
use function is_object;
use function sprintf;
use function strlen;
use function strtr;

/**
 * Provide methods to handle multiple widgets in one.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SubDcaWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_subdca';

    /**
     * Options.
     *
     * @var array
     */
    protected $arrOptions = [];

    /**
     * SubFields.
     *
     * @var array
     */
    protected $arrSubFields = [];

    /**
     * Flag fields to be applied to each subfield.
     *
     * @var array
     */
    protected $arrFlagFields = [];

    /**
     * The prepared widgets.
     *
     * @var array
     */
    protected $arrWidgets = [];

    /**
     * Initialize the object.
     *
     * @param array|bool $attributes The attributes to apply to this widget (optional).
     */
    public function __construct($attributes = false)
    {
        parent::__construct();
        if (is_array($attributes)) {
            $this->addAttributes($attributes);

            // Input field callback.
            if (isset($attributes['getsubfields_callback']) && is_array($attributes['getsubfields_callback'])) {
                $arrCallback = $this->$attributes['getsubfields_callback'];
                if (!is_object($arrCallback[0])) {
                    $this->import($arrCallback[0]);
                }
                $this->arrSubFields = $this->{$arrCallback[0]}->{$arrCallback[1]}($this, $attributes);
            }
        }
    }

    /**
     * Add specific attribute magic setter.
     *
     * In addition to those supported by the Contao Widget class, this
     * widget does understand: 'options', 'subfields' and 'flagfields'.
     *
     * @param string $strKey   The key of the attribute to set.
     * @param mixed  $varValue The value to use.
     *
     * @return void
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'options':
                $this->arrOptions = StringUtil::deserialize($varValue);

                foreach ($this->arrOptions as $arrOptions) {
                    if ($arrOptions['default']) {
                        $this->varValue = $arrOptions['value'];
                    }
                }
                break;
            case 'subfields':
                $this->arrSubFields = StringUtil::deserialize($varValue);
                break;

            case 'flagfields':
                $this->arrFlagFields = StringUtil::deserialize($varValue);
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        return $dispatcher;
    }

    /**
     * Generate a help wizard if needed.
     *
     * @param string $key   The widget name.
     * @param array  $field The field DCA - might get changed within this routine.
     *
     * @return string
     *
     */
    protected function getHelpWizard($key, $field)
    {
        // Add the help wizard.
        if (empty($field['eval']['helpwizard'])) {
            return '';
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);
        $generator = System::getContainer()->get('router');

        $event = new GenerateHtmlEvent(
            'help.svg',
            $translator->trans('helpWizard', [], 'dc-general'),
            'style="vertical-align:text-bottom;"'
        );

        $dispatcher->dispatch($event, ContaoEvents::IMAGE_GET_HTML);
        $property = $this->strName . '_' . $key;
        return strtr(
            ' <a href="{url}" title="{title}" ' .
            'onclick="Backend.openModalIframe({\'title\':\'{windowTitle}\',\'url\':this.href});' .
            'return false">{icon}</a>',
            [
                '{url}'         => $generator->generate(
                    'cca.backend-help',
                    [
                        'table' => $this->strTable,
                        'property' => $property,
                    ]
                ),
                '{title}'       => StringUtil::specialchars($translator->trans('helpWizard', [], 'dc-general')),
                '{windowTitle}' => StringUtil::specialchars(
                    $translator->trans($property . '.label', [], $this->strTable)
                ),
                '{icon}'        => $event->getHtml() ?? ''
            ]
        );
    }

    /**
     * Make fields mandatory if necessary.
     *
     * @param array  $field The field DCA.
     * @param string $row   The setting name.
     * @param string $key   The widget name.
     *
     * @return array
     */
    protected function makeMandatory($field, $row, $key)
    {
        $field['eval']['required'] = false;
        // Use strlen() here (see contao core issue #3277).
        if (empty($field['eval']['mandatory'])) {
            return $field;
        }

        if (is_array($this->varValue[$row][$key])) {
            if (empty($this->varValue[$row][$key])) {
                $field['eval']['required'] = true;
            }
        } elseif ($this->varValue[$row][$key] === '') {
            $field['eval']['required'] = true;
        }

        return $field;
    }

    /**
     * Retrieve the widget class if it is valid.
     *
     * @param array $field The field information.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getWidgetClass($field)
    {
        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        assert($scopeMatcher instanceof ScopeMatcher);

        $requestStack = System::getContainer()->get('request_stack');
        assert($requestStack instanceof RequestStack);

        $isBackend = $scopeMatcher->isBackendRequest($requestStack->getCurrentRequest() ?? Request::create(''));

        /** @var class-string<Widget>|null $strClass */
        $className = $GLOBALS[($isBackend ? 'BE_FFL' : 'TL_FFL')][$field['inputType']];

        if (($className !== '') && class_exists($className)) {
            return $className;
        }

        return '';
    }

    /**
     * Handle the onload_callback.
     *
     * @param array $field The field information.
     * @param mixed $value The value.
     *
     * @return mixed
     */
    protected function handleLoadCallback($field, $value)
    {
        // Load callback.
        if (isset($field['load_callback']) && is_array($field['load_callback'])) {
            foreach ($field['load_callback'] as $callback) {
                $this->import($callback[0]);
                $value = $this->{$callback[0]}->{$callback[1]}($value, $this);
            }
        }

        return $value;
    }

    /**
     * Initialize widget.
     *
     * Based on DataContainer::row() from Contao 2.10.1.
     *
     * @param array  $arrField The field DCA - might get changed within this routine.
     * @param string $strRow   The setting name.
     * @param string $strKey   The widget name.
     * @param mixed  $varValue The widget value.
     *
     * @return Widget|null The widget on success, null otherwise.
     */
    protected function initializeWidget(&$arrField, $strRow, $strKey, $varValue)
    {
        $xlabel = $this->getHelpWizard($strKey, $arrField);

        // Input field callback.
        if (isset($arrField['input_field_callback']) && is_array($arrField['input_field_callback'])) {
            if (!is_object($this->$arrField['input_field_callback'][0])) {
                $this->import($arrField['input_field_callback'][0]);
            }

            return $this->{$arrField['input_field_callback'][0]}->$arrField['input_field_callback'][1]($this, $xlabel);
        }

        /** @var class-string<Widget>|null $strClass */
        $strClass = $this->getWidgetClass($arrField);

        if (empty($strClass)) {
            return null;
        }

        $varValue = $this->handleLoadCallback($arrField, $varValue);
        $arrField = $this->makeMandatory($arrField, $strRow, $strKey);

        $arrField['name']              = $this->strName . '[' . $strRow . '][' . $strKey . ']';
        $arrField['id']                = $this->strId . '_' . $strRow . '_' . $strKey;
        $arrField['value']             = ($varValue !== '') ? $varValue : ($arrField['default'] ?? '');
        $arrField['eval']['tableless'] = true;

        $event = new GetAttributesFromDcaEvent(
            $arrField,
            $arrField['name'],
            $arrField['value'],
            '',
            $this->strTable,
            ((is_a($this->objDca, DataContainer::class)) ? $this->objDca : null)
        );

        $this->getEventDispatcher()->dispatch($event, ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA);

        /** @psalm-suppress UnsafeInstantiation */
        $objWidget = new $strClass($event->getResult());
        if (!($objWidget instanceof Widget)) {
            return null;
        }

        $objWidget->strId       = (int) $arrField['id'];
        $objWidget->storeValues = 1; // The type is int in the widget.
        $objWidget->xlabel      = $xlabel;

        return $objWidget;
    }

    /**
     * Prepare all widgets and store them in the protected $arrWidgets property.
     *
     * @return void
     */
    protected function prepareWidgets()
    {
        if ($this->arrWidgets) {
            return;
        }

        $arrWidgets = [];
        foreach ($this->arrSubFields as $strFieldName => &$arrSubField) {
            $varValue  = $this->value[$strFieldName] ?? [];
            $arrRow = [];
            $objWidget = $this->initializeWidget(
                $arrSubField,
                $strFieldName,
                'value',
                $varValue['value'] ?? null
            );

            if (!$objWidget) {
                continue;
            }
            $arrRow[] = $objWidget;
            foreach ($this->arrFlagFields as $strFlag => $arrFlagField) {
                $objWidget = $this->initializeWidget(
                    $arrFlagField,
                    $strFieldName,
                    $strFlag,
                    $varValue[$strFlag] ?? null
                );

                if ($objWidget) {
                    $arrRow[] = $objWidget;
                }
            }
            $arrWidgets[] = $arrRow;
        }
        $this->arrWidgets = $arrWidgets;
    }

    /**
     * Handle the onsave_callback for a widget.
     *
     * @param array  $field  The field DCA.
     * @param Widget $widget The widget to validate.
     * @param mixed  $value  The value.
     *
     * @return mixed
     */
    protected function handleSaveCallback($field, $widget, $value)
    {
        $newValue = $value;

        if (isset($field['save_callback']) && is_array($field['save_callback'])) {
            foreach ($field['save_callback'] as $callback) {
                $this->import($callback[0]);

                try {
                    $newValue = $this->{$callback[0]}->{$callback[1]}($newValue, $this);
                } catch (Exception $exception) {
                    $widget->addError($exception->getMessage());
                    $this->blnSubmitInput = false;

                    return $value;
                }
            }
        }

        return $newValue;
    }

    /**
     * Validate the value of the widget.
     *
     * Based on DataContainer::row() from Contao 2.10.1
     *
     * @param array  $arrField The field DCA.
     * @param string $strRow   The setting name.
     * @param string $strKey   The widget name.
     * @param mixed  $varInput The overall input value.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function validateWidget(&$arrField, $strRow, $strKey, &$varInput)
    {
        $varValue  = $varInput[$strRow][$strKey] ?? '';
        $objWidget = $this->initializeWidget($arrField, $strRow, $strKey, $varValue);
        if (!is_object($objWidget)) {
            return false;
        }

        // Hack for checkboxes.
        if (($arrField['inputType'] === 'checkbox') && isset($varInput[$strRow][$strKey])) {
            $_POST[$objWidget->name] = $varValue;
        }

        $objWidget->validate();

        $varValue = $objWidget->value;

        // Convert date formats into timestamps (check the eval setting first -> #3063).
        $rgxp = $arrField['eval']['rgxp'] ?? null;
        if (($rgxp === 'date' || $rgxp === 'time' || $rgxp === 'datim') && $varValue !== '') {
            $objDate  = new Date($varValue, $GLOBALS['TL_CONFIG'][$rgxp . 'Format']);
            $varValue = $objDate->tstamp;
        }

        $varValue = $this->handleSaveCallback($arrField, $objWidget, $varValue);

        $varInput[$strRow][$strKey] = $varValue;

        // Do not submit if there are errors.
        if ($objWidget->hasErrors()) {
            return false;
        }

        return true;
    }

    /**
     * Validate the widget.
     *
     * @param mixed $varInput The value to validate.
     *
     * @return mixed The validated data.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function validator($varInput)
    {
        $blnHasError = false;
        foreach ($this->arrSubFields as $strFieldName => &$arrSubField) {
            if (!$this->validateWidget($arrSubField, $strFieldName, 'value', $varInput)) {
                $blnHasError = true;
            }

            foreach ($this->arrFlagFields as $strFlag => $arrFlagField) {
                if (!$this->validateWidget($arrFlagField, $strFieldName, $strFlag, $varInput)) {
                    $blnHasError = true;
                }
            }
        }
        unset($arrSubField);

        if ($blnHasError) {
            $this->blnSubmitInput = false;
            $this->addError($GLOBALS['TL_LANG']['ERR']['general']);
        }
        return $varInput;
    }

    /**
     * Generate the help tag for a widget if needed.
     *
     * @param Widget $widget The widget.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getHelpForWidget($widget)
    {
        if (!empty($GLOBALS['TL_CONFIG']['showHelp']) && !empty($widget->description)) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            return sprintf(
                '<p class="tl_help tl_tip%s">%s</p>',
                (string) $widget->tl_class,
                $widget->description
            );
        }

        return '';
    }

    /**
     *  Build the options for a widget.
     *
     * @return array
     */
    protected function buildOptions()
    {
        $options = [];
        foreach ($this->arrWidgets as $widgetRow) {
            $columns = [];
            foreach ((array) $widgetRow as $widget) {
                /** @var Widget $widget */
                $rawValign = (string) ($widget->valign ?? '');
                $valign    = ($rawValign !== '' ? ' valign="' . $rawValign . '"' : '');
                $rawClass = $widget->class;
                $class    = ($rawClass !== '' ? ' class="' . $rawClass . '"' : '');
                $style    = ($widget->style !== '' ? ' style="' . $widget->style . '"' : '');
                $help     = $this->getHelpForWidget($widget);

                $columns[] = sprintf(
                    '<td %1$s%2$s%3$s>%4$s%5$s</td>',
                    $valign,
                    $class,
                    $style,
                    $widget->parse(),
                    $help
                );
            }
            $options[] = implode('', $columns);
        }

        return $options;
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate()
    {
        $GLOBALS['TL_CSS'][] = '/bundles/metamodelscore/css/style.css';

        $this->prepareWidgets();

        $arrOptions = $this->buildOptions();

        // Add a "no entries found" message if there are no sub widgets.
        if (!count($arrOptions)) {
            $arrOptions[] = '<td><p class="tl_noopt">'
                            . (string) ($GLOBALS['TL_LANG']['MSC']['noResult'] ?? '')
                            . '</p></td>';
        }

        $strHead = '';
        $strBody = sprintf('<tbody><tr>%s</tr></tbody>', implode("</tr>\n<tr>", $arrOptions));

        $strOutput = sprintf(
            '<table%s id="ctrl_%s" class="tl_subdca">%s%s</table>',
            (($this->style) ? ('style="' . $this->style . '"') : ('')),
            $this->strId,
            $strHead,
            $strBody
        );

        return sprintf(
            '<div id="ctrl_%s" class="tl_multiwidget_container%s clr">%s</div>',
            $this->strName,
            (strlen($this->strClass) ? ' ' . $this->strClass : ''),
            $strOutput
        );
    }
}
