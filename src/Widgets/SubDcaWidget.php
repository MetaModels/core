<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Widgets;

use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provide methods to handle multiple widgets in one.
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
    protected $arrOptions = array();

    /**
     * SubFields.
     *
     * @var array
     */
    protected $arrSubFields = array();

    /**
     * Flag fields to be applied to each subfield.
     *
     * @var array
     */
    protected $arrFlagFields = array();

    /**
     * The prepared widgets.
     *
     * @var array
     */
    protected $arrWidgets = array();

    /**
     * Initialize the object.
     *
     * @param array|bool $attributes The attributes to apply to this widget (optional).
     */
    public function __construct($attributes = false)
    {
        parent::__construct();
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

    /**
     * Add specific attribute magic setter.
     *
     * In addition to those supported by the Contao Widget class, this
     * widget does understand: 'options', 'subfields' and 'flagfields'.
     *
     * @param string $strKey   The key of the attribute to set.
     *
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
        return System::getContainer()->get('event_dispatcher');
    }

    /**
     * Generate an help wizard if needed.
     *
     * @param string $key   The widget name.
     *
     * @param array  $field The field DCA - might get changed within this routine.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getHelpWizard($key, $field)
    {
        // Add the help wizard.
        if (empty($field['eval']['helpwizard'])) {
            return '';
        }

        $event = new GenerateHtmlEvent(
            'about.svg',
            $GLOBALS['TL_LANG']['MSC']['helpWizard'],
            'style="vertical-align:text-bottom;"'
        );
        $this->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        return sprintf(
            ' <a href="%shelp.php?table=%s&amp;field=%s_%s" title="%s" rel="lightbox[help 610 80%]">%s</a>',
            TL_PATH . 'contao/',
            $this->strTable,
            $this->strName,
            $key,
            StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard']),
            $event->getHtml()
        );
    }

    /**
     * Make fields mandatory if necessary.
     *
     * @param array  $field The field DCA.
     *
     * @param string $row   The setting name.
     *
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
        } else {
            if (!strlen($this->varValue[$row][$key])) {
                $field['eval']['required'] = true;
            }
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
        $className = $GLOBALS[(TL_MODE == 'BE' ? 'BE_FFL' : 'TL_FFL')][$field['inputType']];

        if (($className !== '') && class_exists($className)) {
            return $className;
        }

        return null;
    }

    /**
     * Handle the onload_callback.
     *
     * @param array $field The field information.
     *
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
     *
     * @param string $strRow   The setting name.
     *
     * @param string $strKey   The widget name.
     *
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

        $strClass = $this->getWidgetClass($arrField);

        if (empty($strClass)) {
            return null;
        }

        $varValue = $this->handleLoadCallback($arrField, $varValue);
        $arrField = $this->makeMandatory($arrField, $strRow, $strKey);

        $arrField['name']              = $this->strName . '[' . $strRow . '][' . $strKey . ']';
        $arrField['id']                = $this->strId . '_' . $strRow . '_' . $strKey;
        $arrField['value']             = ($varValue !== '') ? $varValue : $arrField['default'];
        $arrField['eval']['tableless'] = true;

        $event = new GetAttributesFromDcaEvent(
            $arrField,
            $arrField['name'],
            $arrField['value'],
            null,
            $this->strTable,
            $this->objDca
        );

        $this->getEventDispatcher()->dispatch(ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA, $event);

        $objWidget = new $strClass($event->getResult());

        $objWidget->strId       = $arrField['id'];
        $objWidget->storeValues = true;
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

        $arrWidgets = array();
        foreach ($this->arrSubFields as $strFieldName => &$arrSubField) {
            $varValue  = $this->value[$strFieldName];
            $arrRow    = array();
            $objWidget = $this->initializeWidget(
                $arrSubField,
                $strFieldName,
                'value',
                $varValue['value']
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
                    $varValue[$strFlag]
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
     *
     * @param Widget $widget The widget to validate.
     *
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
                } catch (Exception $e) {
                    $widget->addError($e->getMessage());
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
     *
     * @param string $strRow   The setting name.
     *
     * @param string $strKey   The widget name.
     *
     * @param mixed  $varInput The overall input value.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function validateWidget(&$arrField, $strRow, $strKey, &$varInput)
    {
        $varValue  = $varInput[$strRow][$strKey];
        $objWidget = $this->initializeWidget($arrField, $strRow, $strKey, $varValue);
        if (!is_object($objWidget)) {
            return false;
        }

        // Hack for checkboxes.
        if (($arrField['inputType'] == 'checkbox') && isset($varInput[$strRow][$strKey])) {
            $_POST[$objWidget->name] = $varValue;
        }

        $objWidget->validate();

        $varValue = $objWidget->value;

        // Convert date formats into timestamps (check the eval setting first -> #3063).
        $rgxp = $arrField['eval']['rgxp'];
        if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '') {
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
        if ($GLOBALS['TL_CONFIG']['showHelp'] && $widget->description) {
            return sprintf(
                '<p class="tl_help tl_tip%s">%s</p>',
                $widget->tl_class,
                $widget->description
            );
        }

        return '';
    }

    /**
     *  Build the options for a widget.
     *
     * @return array.
     */
    protected function buildOptions()
    {
        $options = array();
        foreach ($this->arrWidgets as $widgetRow) {
            $columns = array();
            foreach ($widgetRow as $widget) {
                /** @var Widget $widget */
                $valign = ($widget->valign != '' ? ' valign="' . $widget->valign . '"' : '');
                $class  = ($widget->tl_class != '' ? ' class="' . $widget->tl_class . '"' : '');
                $style  = ($widget->style != '' ? ' style="' . $widget->style . '"' : '');
                $help   = $this->getHelpForWidget($widget);

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
        $GLOBALS['TL_CSS'][] = 'bundles/metamodelscore/css/style.css';

        $this->prepareWidgets();

        $arrOptions = $this->buildOptions();

        // Add a "no entries found" message if there are no sub widgets.
        if (!count($arrOptions)) {
            $arrOptions[] = '<td><p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p></td>';
        }

        $strHead = '';
        $strBody = sprintf('<tbody><tr>%s</tr></tbody>', implode("</tr>\n<tr>", $arrOptions));

        $strOutput = sprintf(
            '<table cellspacing="0"%s cellpadding="0" id="ctrl_%s" class="tl_subdca">%s%s</table>',
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
