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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidPropertyValueException;
use MetaModels\Attribute\IAttribute;
use MetaModels\Exceptions\DifferentValuesException;
use MetaModels\IItem;
use MetaModels\ITranslatedMetaModel;

/**
 * Data model class for DC_General <-> MetaModel adaption.
 */
class Model implements ModelInterface
{
    /**
     * The MetaModel item accessible via this instance.
     *
     * @var IItem|null
     */
    protected $objItem = null;

    /**
     * The meta information the DC and views need to buffer in this model.
     *
     * @var array
     */
    protected $arrMetaInformation = [];

    /**
     * The language of the contained data.
     *
     * @var string|null
     */
    private ?string $language;

    /**
     * Return the names of all properties stored within this model.
     *
     * @return string[]
     */
    protected function getPropertyNames()
    {
        $propertyNames = ['id', 'pid', 'tstamp', 'sorting'];

        $item = $this->getItem();
        assert($item instanceof IItem);

        if ($item->getMetaModel()->hasVariants()) {
            $propertyNames[] = 'varbase';
            $propertyNames[] = 'vargroup';
        }

        return \array_merge($propertyNames, \array_keys($item->getMetaModel()->getAttributes()));
    }

    /**
     * Returns the native IMetaModelItem instance encapsulated within this abstraction.
     *
     * @return IItem|null
     */
    public function getItem()
    {
        return $this->objItem;
    }

    /**
     * Create a new instance of this class.
     *
     * @param IItem $objItem The item that shall be encapsulated.
     */
    public function __construct($objItem, ?string $language = null)
    {
        $this->objItem  = $objItem;
        $this->language = $language;
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $item = $this->getItem();
        assert($item instanceof IItem);
        $this->objItem = $item->copy();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        $item = $this->getItem();
        assert($item instanceof IItem);

        return $item->get('id');
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($propertyName)
    {
        if (null !== ($item = $this->getItem())) {
            $varValue = $item->get($propertyName);
            // Test if it is an attribute, if so, let it transform the data for the widget.
            $objAttribute = $item->getAttribute($propertyName);
            if ($objAttribute) {
                $varValue = $objAttribute->valueToWidget($varValue);
            }

            return $varValue;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertiesAsArray()
    {
        $arrResult = [];

        foreach ($this->getPropertyNames() as $strKey) {
            $arrResult[$strKey] = $this->getProperty($strKey);
        }

        return $arrResult;
    }

    /**
     * {@inheritDoc}
     */
    public function getMeta($strMetaName)
    {
        if (\array_key_exists($strMetaName, $this->arrMetaInformation)) {
            return $this->arrMetaInformation[$strMetaName];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($mixId)
    {
        if ($this->getId() === null) {
            $item = $this->getItem();
            assert($item instanceof IItem);
            $item->set('id', $mixId);
            $this->setMeta(static::IS_CHANGED, true);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidPropertyValueException When the property is unable to accept the value.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function setProperty($strPropertyName, $varValue)
    {
        if (null !== ($item = $this->getItem())) {
            $varInternalValue = $varValue;
            // Test if it is an attribute, if so, let it transform the data for the widget.
            $objAttribute = $item->getAttribute($strPropertyName);

            if (null !== $objAttribute) {
                $model            = $objAttribute->getMetaModel();
                $originalLanguage = null;
                /** @psalm-suppress DeprecatedMethod */
                if (null !== $this->language) {
                    if ($model instanceof ITranslatedMetaModel) {
                        $originalLanguage = $model->selectLanguage($this->language);
                        /** @psalm-suppress DeprecatedMethod */
                    } elseif ($model->isTranslated()) {
                        $originalLanguage       = $GLOBALS['TL_LANGUAGE'];
                        $GLOBALS['TL_LANGUAGE'] = $this->language;
                    }
                }

                $varInternalValue = $objAttribute->widgetToValue($varValue, $item->get('id'));
            }

            try {
                if ($varValue !== $this->getProperty($strPropertyName)) {
                    $this->setMeta(static::IS_CHANGED, true);
                    $item->set($strPropertyName, $varInternalValue);
                    try {
                        DifferentValuesException::compare($varValue, $this->getProperty($strPropertyName), false);
                    } catch (DifferentValuesException $exception) {
                        throw new DcGeneralInvalidPropertyValueException(
                            \sprintf(
                                'Property %s (%s) did not accept the value (%s).',
                                $strPropertyName,
                                $objAttribute ? ((string) $objAttribute->get('type')) : '?',
                                $exception->getLongMessage()
                            ),
                            1,
                            $exception
                        );
                    }
                }
            } finally {
                if (isset($originalLanguage, $model)) {
                    if ($model instanceof ITranslatedMetaModel) {
                        $model->selectLanguage($originalLanguage);
                    } else {
                        $GLOBALS['TL_LANGUAGE'] = $originalLanguage;
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertiesAsArray($properties)
    {
        foreach ($properties as $strKey => $varValue) {
            $this->setProperty($strKey, $varValue);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setMeta($strMetaName, $varValue)
    {
        $this->arrMetaInformation[$strMetaName] = $varValue;
    }

    /**
     * {@inheritDoc}
     */
    public function hasProperties()
    {
        return (bool) $this->getItem();
    }

    /**
     * {@inheritDoc}
     *
     * @return \Traversable<string, mixed>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ModelIterator($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName()
    {
        $item = $this->getItem();
        assert($item instanceof IItem);

        return $item->getMetaModel()->getTableName();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a property in the value bag has been marked as invalid.
     */
    public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach ($this->getPropertyNames() as $property) {
            if (!$valueBag->hasPropertyValue($property)) {
                continue;
            }

            if ($valueBag->isPropertyValueInvalid($property)) {
                throw new DcGeneralInvalidArgumentException('The value for property ' . $property . ' is invalid.');
            }

            $this->setProperty($property, $valueBag->getPropertyValue($property));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach ($this->getPropertyNames() as $property) {
            if (!$valueBag->hasPropertyValue($property)) {
                continue;
            }

            $valueBag->setPropertyValue($property, $this->getProperty($property));
        }

        return $this;
    }
}
