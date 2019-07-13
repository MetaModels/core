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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IInternal;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Filter\IFilter;
use MetaModels\Helper\EmptyTest;
use MetaModels\Render\Setting\ICollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface for a MetaModel item.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
     * The event dispatcher.
     *
     * @var null|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param IMetaModel                    $objMetaModel The model this item is represented by.
     * @param array|null                    $arrData      The initial data that shall be injected into the new instance.
     * @param EventDispatcherInterface|null $dispatcher   The event dispatcher.
     */
    public function __construct(IMetaModel $objMetaModel, $arrData, EventDispatcherInterface $dispatcher = null)
    {
        $this->arrData    = $arrData;
        $this->metaModel  = $objMetaModel;
        $this->dispatcher = $dispatcher;

        if (null === $dispatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the event dispatcher as 3rd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        if (null === $arrData) {
            // Initialize attributes with empty values.
            $arrData = [];

            foreach ($objMetaModel->getAttributes() as $attribute) {
                $arrData[$attribute->getColName()] = null;
            }
        }

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
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated and will get removed in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $this->getMetaModel()->getServiceContainer();
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        if ($this->dispatcher) {
            return $this->dispatcher;
        }

        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * Helper function for {@see MetaModelItem::parseValue()} and {@see MetaModelItem::parseAttribute()}.
     *
     * @param IAttribute       $objAttribute    The attribute to parse.
     *
     * @param string           $strOutputFormat The desired output format.
     *
     * @param ICollection|null $objSettings     The settings object to be applied.
     *
     * @return array The parsed information for the given attribute.
     */
    public function internalParseAttribute($objAttribute, $strOutputFormat, $objSettings)
    {
        if ($objAttribute instanceof IInternal) {
            return array();
        }

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
            && EmptyTest::isEmptyValue($arrResult['raw'])
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
     *
     * @deprecated Use \MetaModels\Helper\EmptyTest::isEmptyValue instead.
     */
    protected function isEmptyValue($mixValue)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            __METHOD__ . ' has been deprecated, use "\MetaModels\Helper\EmptyTest::isEmptyValue()" instead',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return EmptyTest::isEmptyValue($mixValue);
    }

    /**
     * Run through each level of an array and check if we have at least one empty value.
     *
     * @param array $arrArray The array to check.
     *
     * @return boolean True => empty, False => some values found.
     *
     * @deprecated Use \MetaModels\Helper\EmptyTest::isArrayEmpty instead.
     */
    protected function isArrayEmpty($arrArray)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            __METHOD__ . ' has been deprecated, use "\MetaModels\Helper\EmptyTest::isArrayEmpty()" instead',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        if (!is_array($arrArray)) {
            // This should never happen but was possible before.
            return EmptyTest::isEmptyValue($arrArray);
        }

        return EmptyTest::isArrayEmpty($arrArray);
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
     * Check if the given attribute is set. This mean if in the data array
     * is the filed set or not. If the attribute is not loaded the function
     * will return false.
     *
     * @param string $strAttributeName The desired attribute.
     *
     * @return bool True means the data is set, on load of the item or at any time.
     *              False means the attribute is not set.
     */
    public function isAttributeSet($strAttributeName)
    {
        return array_key_exists($strAttributeName, $this->arrData);
    }

    /**
     * Return a list of the col names from the attributes which are set.
     * Including all meta field as well.
     *
     * @return array
     */
    public function getSetAttributes()
    {
        return array_keys($this->arrData);
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
     * @return IItems|null A list of all variants for this item.
     */
    public function getVariants($objFilter)
    {
        if ($this->isVariantBase()) {
            return $this->getMetaModel()->findVariants(array($this->get('id')), $objFilter);
        }

        return null;
    }

    /**
     * Fetch the meta model variant base for this item.
     *
     * Note: For a non-variant item the variant base is the item itself.
     *
     * @return IItem The variant base.
     */
    public function getVariantBase()
    {
        if ($this->getMetaModel()->hasVariants() && !$this->isVariantBase()) {
            return $this->getMetaModel()->findById($this->get('vargroup'));
        }

        return $this;
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
     * @param int|null $timestamp Optional the timestamp.
     *
     * @return void
     */
    public function save($timestamp = null)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $objMetaModel = $this->getMetaModel();
        $objMetaModel->saveItem($this, $timestamp);
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

        $arrResult = [
            'raw'            => $this->arrData,
            'text'           => [],
            'attributes'     => [],
            $strOutputFormat => [],
            'class'          => '',
            'actions'        => []
        ];

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

        // Add jumpTo link
        $jumpTo = $this->buildJumpToLink($objSettings);
        if (true === $jumpTo['deep']) {
            $arrResult['actions']['jumpTo'] = [
                'href'  => $jumpTo['url'],
                'label' => $this->getCaptionText('details'),
                'class' => 'details'
            ];
        }

        // Just here for backwards compatibility with templates. See #1087
        $arrResult['jumpTo'] = $jumpTo;

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

        // Add css classes, i.e. for the frontend editing list.
        if ($this->getMetaModel()->hasVariants()) {
            $arrResult['class'] = $this->variantCssClass();
        }

        // Trigger event to allow other extensions to manipulate the parsed data.
        $event = new ParseItemEvent($objSettings, $this, $strOutputFormat, $arrResult);
        $this->getEventDispatcher()->dispatch(MetaModelsEvents::PARSE_ITEM, $event);

        return $event->getResult();
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

        return $objSettings->buildJumpToUrlFor($this);
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
        unset($arrNewData['vargroup']);
        return new Item($this->getMetaModel(), $arrNewData, $this->dispatcher);
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
            $objNewItem->set('varbase', '0');
        } else {
            $objNewItem->set('vargroup', $this->get('vargroup'));
            $objNewItem->set('varbase', '0');
        }
        return $objNewItem;
    }


    /**
     * Retrieve the translation string for the given lang key.
     *
     * In order to achieve the correct caption text, the function tries several translation strings sequentially.
     * The first language key that is set will win, even if it is to be considered empty.
     *
     * This message is looked up in the following order:
     * 1. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][<render settings id>][$langKey]
     * 2. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][$langKey]
     * 3. $GLOBALS['TL_LANG']['MSC'][$langKey]
     *
     * @param string $langKey The language key to retrieve.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getCaptionText($langKey)
    {
        $tableName = $this->getMetaModel()->getTableName();
        if (isset($this->objView)
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName][$langKey])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$langKey];
        }

        return $GLOBALS['TL_LANG']['MSC'][$langKey];
    }

    /**
     * Create the CSS class for variant information.
     *
     * @return string
     */
    private function variantCssClass()
    {
        if ($this->isVariant()) {
            return 'variant';
        }
        if ($this->isVariantBase()) {
            $result = 'varbase';

            if (0 !== $this->getVariants(null)->getCount()) {
                $result .= ' varbase-with-variants';
            }
            return $result;
        }

        return '';
    }
}
