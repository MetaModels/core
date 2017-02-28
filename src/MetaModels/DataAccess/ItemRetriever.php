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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DataAccess;

use Contao\Database;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ISimple;
use MetaModels\Attribute\ITranslated;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\Items;
use RuntimeException;

/**
 * This class handles the item retrieval
 *
 * @internal Not part of the API.
 */
class ItemRetriever
{
    use DatabaseHelperTrait;

    /**
     * The metamodel we work on.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The MetaModel table name.
     *
     * @var string
     */
    private $tableName;

    /**
     * The used database.
     *
     * @var Database
     */
    private $database;

    /**
     * The attribute names.
     *
     * @var IAttribute[]
     */
    private $attributes;

    /**
     * The simple attribute names.
     *
     * @var ISimple[]
     */
    private $simpleAttributes;

    /**
     * Create a new instance.
     *
     * @param IMetaModel $metaModel The metamodel we work on.
     * @param Database   $database  The used database.
     */
    public function __construct(IMetaModel $metaModel, Database $database)
    {
        $this->metaModel = $metaModel;
        $this->tableName = $metaModel->getTableName();
        $this->database  = $database;
        $this->setAttributes(array_keys($metaModel->getAttributes()));
    }

    /**
     * Set the attribute names.
     *
     * @param string[] $attributeNames The attribute names.
     *
     * @return ItemRetriever
     */
    public function setAttributes(array $attributeNames)
    {
        $this->attributes       = [];
        $this->simpleAttributes = [];

        foreach ($this->metaModel->getAttributes() as $name => $attribute) {
            if (!in_array($name, $attributeNames)) {
                continue;
            }
            $this->attributes[$name] = $attribute;
            if ($attribute instanceof ISimple) {
                $this->simpleAttributes[$name] = $attribute;
            }
        }

        return $this;
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param IdResolver $resolver The ids of the items to retrieve the order of ids is used for sorting of the
     *                             return values.
     *
     * @return IItems a collection of all matched items, sorted by the id list.
     */
    public function findItems(IdResolver $resolver)
    {
        $ids = $resolver->getIds();

        if (!$ids) {
            return new Items([]);
        }

        $result = $this->fetchRows($ids);
        // Determine "independent attributes" (complex and translated) and inject their content into the row.
        $result = $this->fetchAdditionalAttributes($ids, $result);
        $items  = [];
        foreach ($result as $entry) {
            $items[] = new Item($this->metaModel, $entry);
        }

        $objItems = new Items($items);

        return $objItems;
    }

    /**
     * Fetch the "native" database rows with the given ids.
     *
     * @param string[] $ids The ids of the items to retrieve the order of ids is used for sorting of the return
     *                      values.

     * @return array an array containing the database rows with each column "deserialized".
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function fetchRows(array $ids)
    {
        // If we have an attribute restriction, make sure we keep the system columns. See #196.
        $system = ['id', 'pid', 'tstamp', 'sorting'];
        if ($this->metaModel->hasVariants()) {
            $system[] = 'varbase';
            $system[] = 'vargroup';
        }
        $attributes = array_merge($system, array_keys($this->simpleAttributes));

        $rows = $this
            ->database
            ->prepare(
                sprintf(
                    'SELECT %1$s FROM %2$s WHERE id IN (%3$s) ORDER BY FIELD(id,%3$s)',
                    implode(', ', $attributes),
                    $this->tableName,
                    $this->buildDatabaseParameterList($ids)
                )
            )
            ->execute(array_merge($ids, $ids));

        if (0 === $rows->numRows) {
            return [];
        }

        $result = [];
        do {
            $data = [];
            foreach ($system as $key) {
                $data[$key] = $rows->$key;
            }
            foreach ($this->simpleAttributes as $key => $attribute) {
                $data[$key] = $attribute->unserializeData($rows->$key);
            }
            $result[$rows->id] = $data;
        } while ($rows->next());

        return $result;
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param string[] $ids    The ids of the items to retrieve the order of ids is used for sorting of the
     *                         return values.
     * @param array    $result The current values.
     *
     * @return array an array of all matched items, sorted by the id list.
     *
     * @throws RuntimeException When an attribute is neither translated nor complex.
     */
    private function fetchAdditionalAttributes(array $ids, array $result)
    {
        $attributeNames = array_diff(array_keys($this->attributes), array_keys($this->simpleAttributes));
        $attributes     = array_filter($this->attributes, function ($attribute) use ($attributeNames) {
            /** @var IAttribute $attribute */
            return in_array($attribute->getColName(), $attributeNames);
        });

        foreach ($attributes as $attributeName => $attribute) {
            /** @var IAttribute $attribute */
            $attributeName = $attribute->getColName();

            switch (true) {
                case ($attribute instanceof ITranslated):
                    $attributeData = $this->fetchTranslatedAttributeValues($attribute, $ids);
                    break;
                case ($attribute instanceof IComplex):
                    $attributeData = $attribute->getDataFor($ids);
                    break;
                default:
                    throw new RuntimeException('Unknown attribute type ' . get_class($attribute));
            }

            foreach (array_keys($result) as $id) {
                $result[$id][$attributeName] = isset($attributeData[$id]) ? $attributeData[$id] : null;
            }
        }

        return $result;
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param ITranslated $attribute The attribute to fetch the values for.
     *
     * @param string[]    $ids       The ids of the items to retrieve the order of ids is used for sorting of the return
     *                               values.
     *
     * @return array an array of all matched items, sorted by the id list.
     */
    private function fetchTranslatedAttributeValues(ITranslated $attribute, array $ids)
    {
        $attributeData = $attribute->getTranslatedDataFor($ids, $this->metaModel->getActiveLanguage());
        $missing       = array_diff($ids, array_keys($attributeData));

        if ($missing) {
            $attributeData += $attribute->getTranslatedDataFor($missing, $this->metaModel->getFallbackLanguage());
        }

        return $attributeData;
    }
}
