<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Render\Setting\ICollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface for a MetaModel item.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Item implements IItem
{
    /**
     * The MetaModel instance attached to the item.
     *
     * Get's populated with the first call to getMetaModel() (lazy initialization).
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The data array containing the raw values obtained from the attributes.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * Create a new instance.
     *
     * @param IMetaModel $objMetaModel The model this item is represented by.
     *
     * @param array      $arrData      The initial data that shall be injected into the new instance.
     */
    public function __construct(IMetaModel $objMetaModel, $arrData)
    {
        $this->arrData   = $arrData;
        $this->metaModel = $objMetaModel;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->getMetaModel()->getServiceContainer();
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * Helper function for {@see MetaModelItem::parseValue()} and {@see MetaModelItem::parseAttribute()}.
     *
     * @param IAttribute  $objAttribute    The attribute to parse.
     *
     * @param string      $strOutputFormat The desired output format.
     *
     * @param ICollection $objSettings     The settings object to be applied.
     *
     * @return array The parsed information for the given attribute.
     */
    public function internalParseAttribute($objAttribute, $strOutputFormat, $objSettings)
    {
        $arrResult = array();
        if ($objAttribute) {
            // Extract view settings for this attribute.
            if ($objSettings) {
                $objAttributeSettings = $objSettings->getSetting($objAttribute->getColName());
            } else {
                $objAttributeSettings = null;
            }
            foreach ($objAttribute->parseValue(
                $this->arrData,
                $strOutputFormat,
                $objAttributeSettings
            ) as $strKey => $varValue) {
                $arrResult[$strKey] = $varValue;
            }
        }

        // If "hideEmptyValues" is true and the raw is empty remove text and output format.
        if (($objSettings instanceof ICollection)
            && $objSettings->get('hideEmptyValues')
            && $this->isEmptyValue($arrResult['raw'])
        ) {
            unset($arrResult[$strOutputFormat]);
            unset($arrResult['text']);
        }

        return $arrResult;
    }

    /**
     * Check if a value is empty.
     *
     * @param array $mixValue The value.
     *
     * @return boolean True => empty, false => found a valid values
     */
    protected function isEmptyValue($mixValue)
    {
        if (is_array($mixValue)) {
            return $this->isArrayEmpty($mixValue);
        } elseif ($mixValue === '') {
            return true;
        } elseif ($mixValue === null) {
            return true;
        }

        return false;
    }

    /**
     * Run through each level of an array and check if we have at least one empty value.
     *
     * @param array $arrArray The array to check.
     *
     * @return boolean True => empty, False => some values found.
     */
    protected function isArrayEmpty($arrArray)
    {
        // First off check for simple types.
        if (empty($arrArray)) {
            return true;
        }
        // Next check for a value array.
        if (is_array($arrArray) && array_key_exists('value', $arrArray)) {
            return $this->isArrayEmpty($arrArray['value']);
        }
        // Now check sub arrays.
        if (is_array($arrArray)) {
            foreach ($arrArray as $value) {
                if (is_array($value)) {
                    return $this->isArrayEmpty($value);
                } elseif (!empty($value)) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Return the native value of an attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return mixed
     */
    public function get($strAttributeName)
    {
        return array_key_exists($strAttributeName, $this->arrData) ? $this->arrData[$strAttributeName] : null;
    }

    /**
     * Set the native value of an Attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @param mixed  $varValue         The value of the attribute.
     *
     * @return IItem
     */
    public function set($strAttributeName, $varValue)
    {
        $this->arrData[$strAttributeName] = $varValue;

        return $this;
    }

    /**
     * Fetch the MetaModel that this item is originating from.
     *
     * @return IMetaModel the instance.
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Fetch the MetaModel attribute instance with the given name.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return IAttribute The instance.
     */
    public function getAttribute($strAttributeName)
    {
        return $this->getMetaModel()->getAttribute($strAttributeName);
    }

    /**
     * Determines if this item is a variant of another item.
     *
     * @return bool True if it is an variant, false otherwise.
     */
    public function isVariant()
    {
        return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '0');
    }

    /**
     * Determines if this item is variant base of other items.
     *
     * Note: this does not mean that there actually exist variants of
     * this item. It merely simply states, that this item is able
     * to function as variant base for other items.
     *
     * @return bool True if it is an variant base, false otherwise.
     */
    public function isVariantBase()
    {
        return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '1');
    }

    /**
     * Fetch the meta model variants for this item.
     *
     * @param IFilter $objFilter The filter settings to be applied.
     *
     * @return IItems A list of all variants for this item.
     */
    public function getVariants($objFilter)
    {
        if ($this->isVariantBase()) {
            return $this->getMetaModel()->findVariants(array($this->get('id')), $objFilter);
        }

        return null;
    }

    /**
     * Find all Variants including the variant base.
     *
     * The item itself is excluded from the return list.
     *
     * @param IFilter $objFilter The additional filter settings to apply.
     *
     * @return null|IItems
     */
    public function getSiblings($objFilter)
    {
        if (!$this->getMetaModel()->hasVariants()) {
            return null;
        }
        return $this->getMetaModel()->findVariantsWithBase(array($this->get('id')), $objFilter);

    }

    /**
     * Save the current data for every attribute to the data sink.
     *
     * @return void
     */
    public function save()
    {
        $objMetaModel = $this->getMetaModel();
        $objMetaModel->saveItem($this);
    }

    /**
     * Register the assets in Contao.
     *
     * @param ICollection|null $objSettings The render settings to use.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function registerAssets($objSettings)
    {
        if (!$objSettings) {
            return;
        }

        // Include CSS.
        $arrCss = $objSettings->get('additionalCss');

        foreach ((array) $arrCss as $arrFile) {
            if ($arrFile['published']) {
                $GLOBALS['TL_CSS'][md5($arrFile['file'])] = $arrFile['file'];
            }
        }

        // Include JS.
        $arrJs = $objSettings->get('additionalJs');

        foreach ((array) $arrJs as $arrFile) {
            if ($arrFile['published']) {
                $GLOBALS['TL_JAVASCRIPT'][md5($arrFile['file'])] = $arrFile['file'];
            }
        }
    }

    /**
     * Renders the item in the given output format.
     *
     * @param string      $strOutputFormat The desired output format (optional - default: text).
     *
     * @param ICollection $objSettings     The render settings to use (optional - default: null).
     *
     * @return array attribute name => format => value
     */
    public function parseValue($strOutputFormat = 'text', $objSettings = null)
    {
        $this->registerAssets($objSettings);

        $arrResult = array
        (
            'raw' => $this->arrData,
            'text' => array(),
            'attributes' => array(),
            $strOutputFormat => array(),
            'class' => ''
        );

        // No render settings, parse "normal" and hope the best - not all attribute types must provide usable output.
        if (!$objSettings) {
            foreach ($this->getMetaModel()->getAttributes() as $objAttribute) {
                $arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
                foreach ($this->internalParseAttribute($objAttribute, $strOutputFormat, null) as $strKey => $varValue) {
                    $arrResult[$strKey][$objAttribute->getColName()] = $varValue;
                }
            }
            return $arrResult;
        }

        $arrResult['jumpTo'] = $this->buildJumpToLink($objSettings);
        $objSettings->setJumpTo($arrResult['jumpTo']);

        // First, parse the values in the same order as they are in the render settings.
        foreach ($objSettings->getSettingNames() as $strAttrName) {
            $objAttribute = $this->getMetaModel()->getAttribute($strAttrName);
            if ($objAttribute) {
                $arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
                foreach ($this->internalParseAttribute(
                    $objAttribute,
                    $strOutputFormat,
                    $objSettings
                ) as $strKey => $varValue) {
                    $arrResult[$strKey][$objAttribute->getColName()] = $varValue;
                }
            }
        }

        // Call HOOK for other extensions to inject data.
        $this->parseValueHook($arrResult, $strOutputFormat, $objSettings);

        return $arrResult;
    }

    /**
     * HOOK handler for third party extensions to inject data into the generated output or to reformat the output.
     *
     * @param array       $arrResult   The generated data.
     *
     * @param string      $strFormat   The desired output format (text, html, etc.).
     *
     * @param ICollection $objSettings The render settings to use.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function parseValueHook(&$arrResult, $strFormat, $objSettings)
    {
        // HOOK: let third party extensions manipulate the generated data.
        if (!empty($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
            && is_array($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
        ) {
            foreach ($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'] as $arrHook) {
                $strClass  = $arrHook[0];
                $strMethod = $arrHook[1];

                if (in_array('getInstance', get_class_methods($strClass))) {
                    $objHook = call_user_func(array($strClass, 'getInstance'));
                } else {
                    $objHook = new $strClass();
                }
                $objHook->$strMethod($arrResult, $this, $strFormat, $objSettings);
            }
        }
    }

    /**
     * Build the jump to array for the given values.
     *
     * @param array|null  $page             The page model of the jumpTo page.
     *
     * @param int         $filterSettingsId The id of the filter settings to use.
     *
     * @param ICollection $renderSettings   The render settings to use.
     *
     * @param string      $language         The language to use.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildJumpToParametersAndUrl($page, $filterSettingsId, $renderSettings, $language)
    {
        if (!$page) {
            return null;
        }

        $result     = array();
        $parameters = '';

        if ($filterSettingsId) {
            $filterSettings = $this->getServiceContainer()->getFilterFactory()->createCollection($filterSettingsId);
            $parameterList  = $filterSettings->generateFilterUrlFrom($this, $renderSettings);

            foreach ($parameterList as $strKey => $strValue) {
                if ($strKey == 'auto_item') {
                    $parameters = '/' . $strValue . $parameters;
                } else {
                    $parameters .= sprintf('/%s/%s', $strKey, $strValue);
                }
            }

            $tableName        = $this->getMetaModel()->getTableName();
            $result['params'] = $parameterList;
            $result['deep']   = (strlen($parameters) > 0);
            if (isset($GLOBALS['TL_LANG']['MSC'][$tableName][$renderSettings->get('id')]['details'])) {
                $result['label'] = $GLOBALS['TL_LANG']['MSC'][$tableName][$renderSettings->get('id')]['details'];
            } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName]['details'])) {
                $result['label'] = $GLOBALS['TL_LANG']['MSC'][$tableName]['details'];
            } else {
                $result['label'] = $GLOBALS['TL_LANG']['MSC']['details'];
            }
        }

        $result['page'] = $page['id'];

        $event = new GenerateFrontendUrlEvent($page, $parameters, $language);
        $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);
        $result['url'] = $event->getUrl();

        return $result;
    }

    /**
     * Build the jumpTo link for use in templates.
     *
     * The returning array will hold the following keys:
     * * params - the url parameter (only if a valid filter setting could be determined).
     * * deep   - boolean true, if parameters are non empty, false otherwise.
     * * page   - id of the jumpTo page.
     * * url    - the complete generated url
     *
     * @param ICollection $objSettings The render settings to use.
     *
     * @return array
     */
    public function buildJumpToLink($objSettings)
    {
        if (!$objSettings) {
            return null;
        }

        // Get the right jumpto.
        $strDesiredLanguage  = $this->getMetaModel()->getActiveLanguage();
        $strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

        $intJumpTo         = 0;
        $intFilterSettings = 0;
        $language          = null;

        foreach ((array) $objSettings->get('jumpTo') as $arrJumpTo) {
            // If either desired language or fallback, keep the result.
            if (!$this->getMetaModel()->isTranslated()
                || $arrJumpTo['langcode'] == $strDesiredLanguage
                || $arrJumpTo['langcode'] == $strFallbackLanguage) {
                $intJumpTo         = $arrJumpTo['value'];
                $intFilterSettings = $arrJumpTo['filter'];
                $language          = $arrJumpTo['langcode'];
                // If the desired language, break. Otherwise try to get the desired one until all have been evaluated.
                if ($strDesiredLanguage == $arrJumpTo['langcode']) {
                    break;
                }
            }
        }

        $event = new GetPageDetailsEvent($intJumpTo);

        if ($intJumpTo) {
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);
        }

        // Apply jumpTo urls based upon the filter defined in the render settings.
        return $this->buildJumpToParametersAndUrl(
            $event->getPageDetails(),
            $intFilterSettings,
            $objSettings,
            $language
        );
    }

    /**
     * Renders a single attribute in the given output format.
     *
     * @param string      $strAttributeName The desired attribute.
     *
     * @param string      $strOutputFormat  The desired output format (optional - default: text).
     *
     * @param ICollection $objSettings      The render settings to use (optional - default: null).
     *
     * @return array format=>value
     */
    public function parseAttribute($strAttributeName, $strOutputFormat = 'text', $objSettings = null)
    {
        return $this->internalParseAttribute($this->getAttribute($strAttributeName), $strOutputFormat, $objSettings);
    }

    /**
     * Returns a new item containing the same values as this item but no id.
     *
     * This is useful when creating new items that shall be based upon another item.
     *
     * @return IItem the new copy.
     */
    public function copy()
    {
        // Fetch data, clean undesired fields and return the new item.
        $arrNewData = $this->arrData;
        unset($arrNewData['id']);
        unset($arrNewData['tstamp']);
        return new Item($this->getMetaModel(), $arrNewData);
    }

    /**
     * Returns a new item containing the same values as this item but no id.
     *
     * Additionally, the item will be a variant child of this item.
     *
     * NOTE: if this item is not a variant base itself, this item will return a item
     * that is a child of this items variant base. i.e. exact clone.
     *
     * @return \MetaModels\IItem the new copy.
     */
    public function varCopy()
    {
        $objNewItem = $this->copy();
        // If this item is a variant base, we need to clean the variant base and set ourselves as the base.
        if ($this->isVariantBase()) {
            $objNewItem->set('vargroup', $this->get('id'));
            $objNewItem->set('varbase', 0);
        } else {
            $objNewItem->set('vargroup', $this->get('vargroup'));
            $objNewItem->set('varbase', 0);
        }
        return $objNewItem;
    }
}
