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
use MetaModels\IItem;
use MetaModels\IMetaModel;

/**
 * This class handles the raw database interaction for MetaModels.
 *
 * @internal Not part of the API.
 */
class ItemPersister
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
    }

    /**
     * Save an item into the database.
     *
     * @param IItem $item The item to save to the database.
     *
     * @return void
     */
    public function saveItem(IItem $item)
    {
        $baseAttributes = false;
        $item->set('tstamp', time());
        if (null === $item->get('id')) {
            $baseAttributes = true;
            $this->createNewItem($item);
        }

        $itemId = $item->get('id');
        $data   = ['tstamp' => $item->get('tstamp')];
        // Update system columns.
        if (null !== $item->get('pid')) {
            $data['pid'] = $item->get('pid');
        }
        if (null !== $item->get('sorting')) {
            $data['sorting'] = $item->get('sorting');
        }
        $this->saveRawColumns($data, [$itemId]);
        unset($data);

        if ($this->metaModel->isTranslated()) {
            $language = $this->metaModel->getActiveLanguage();
        } else {
            $language = null;
        }

        $variantIds = [];
        if ($item->isVariantBase()) {
            $variants = $this->metaModel->findVariantsWithBase([$itemId], null);
            foreach ($variants as $objVariant) {
                /** @var IItem $objVariant */
                $variantIds[] = $objVariant->get('id');
            }
            $this->saveRawColumns(['tstamp' => $item->get('tstamp')], $variantIds);
        }

        $this->updateVariants($item, $language, $variantIds, $baseAttributes);

        // Tell all attributes that the model has been saved. Useful for alias fields, edit counters etc.
        foreach ($this->metaModel->getAttributes() as $objAttribute) {
            if ($item->isAttributeSet($objAttribute->getColName())) {
                $objAttribute->modelSaved($item);
            }
        }
    }

    /**
     * Remove an item from the database.
     *
     * @param IItem $item The item to delete from the database.
     *
     * @return void
     */
    public function deleteItem(IItem $item)
    {
        $idList = [$item->get('id')];
        // Determine if the model is a variant base and if so, fetch the variants additionally.
        if ($item->isVariantBase()) {
            $variants = $this->metaModel->findVariants([$item->get('id')], null);
            foreach ($variants as $variant) {
                /** @var IItem $variant */
                $idList[] = $variant->get('id');
            }
        }
        // Complex and translated attributes shall delete their values first.
        $this->deleteAttributeValues($idList);
        // Now make the real rows disappear.
        $this
            ->database
            ->prepare(sprintf(
                'DELETE FROM %s WHERE id IN (%s)',
                $this->tableName,
                $this->buildDatabaseParameterList($idList)
            ))
            ->execute($idList);
    }


    /**
     * Create a new item in the database.
     *
     * @param IItem $item The item to be created.
     *
     * @return void
     */
    private function createNewItem(IItem $item)
    {
        $data = ['tstamp' => $item->get('tstamp')];

        $isNewBaseItem = false;
        if ($this->metaModel->hasVariants()) {
            // No variant group is given, so we have a complete new base item this should be a workaround for these
            // values should be set by the GeneralDataMetaModel or whoever is calling this method.
            if (null === $item->get('vargroup')) {
                $item->set('varbase', '1');
                $item->set('vargroup', '0');
                $isNewBaseItem = true;
            }
            $data['varbase']  = $item->get('varbase');
            $data['vargroup'] = $item->get('vargroup');
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $itemId = $this
            ->database
            ->prepare('INSERT INTO ' . $this->tableName . ' %s')
            ->set($data)
            ->execute()
            ->insertId;
        $item->set('id', $itemId);

        // Set the variant group equal to the id.
        if ($isNewBaseItem) {
            $this->saveRawColumns(['vargroup' => $item->get('id')], [$item->get('id')]);
            $item->set('vargroup', $item->get('id'));
        }
    }

    /**
     * Update the values of a native columns for the given ids.
     *
     * @param string[] $columns The column names to update (i.e. tstamp) as key, the values as value.
     *
     * @param string[] $ids     The ids of the items that shall be updated.
     *
     * @return void
     */
    private function saveRawColumns(array $columns, array $ids)
    {
        $this
            ->database
            ->prepare(
                sprintf(
                    'UPDATE %1$s %%s=? WHERE id IN (%2$s)',
                    $this->tableName,
                    $this->buildDatabaseParameterList($ids)
                )
            )
            ->set($columns)
            ->execute($ids);
    }

    /**
     * Update the variants with the value if needed.
     *
     * @param IItem    $item           The item to save.
     * @param string   $activeLanguage The language the values are in.
     * @param string[] $variantIds     The ids of all variants.
     * @param bool     $baseAttributes If also the base attributes get updated as well.
     *
     * @return void
     */
    private function updateVariants(IItem $item, $activeLanguage, array $variantIds, $baseAttributes)
    {
        list($variant, $invariant) = $this->splitAttributes($item, $baseAttributes);

        // Override in variants.
        foreach ($variant as $attributeName => $attribute) {
            $this->saveAttributeValues($attribute, $variantIds, $item->get($attributeName), $activeLanguage);
        }
        // Save invariant ones now.
        $ids = [$item->get('id')];
        foreach ($invariant as $attributeName => $attribute) {
            $this->saveAttributeValues($attribute, $ids, $item->get($attributeName), $activeLanguage);
        }
    }

    /**
     * Update an attribute for the given ids with the given data.
     *
     * @param IAttribute $attribute The attribute to save.
     * @param array      $ids       The ids of the rows that shall be updated.
     * @param mixed      $data      The data to save in raw data.
     * @param string     $language  The language code to save.
     *
     * @return void
     *
     * @throws \RuntimeException When an unknown attribute type is encountered.
     */
    private function saveAttributeValues($attribute, array $ids, $data, $language)
    {
        // Call the serializeData for all simple attributes.
        if ($attribute instanceof ISimple) {
            $data = $attribute->serializeData($data);
        }

        $arrData = array();
        foreach ($ids as $intId) {
            $arrData[$intId] = $data;
        }

        // Check for translated fields first, then for complex and save as simple then.
        if ($language && $attribute instanceof ITranslated) {
            $attribute->setTranslatedDataFor($arrData, $language);
            return;
        }
        if ($attribute instanceof IComplex) {
            $attribute->setDataFor($arrData);
            return;
        }
        if ($attribute instanceof ISimple) {
            $attribute->setDataFor($arrData);
            return;
        }

        throw new \RuntimeException(
            'Unknown attribute type, can not save. Interfaces implemented: ' .
            implode(', ', class_implements($attribute))
        );
    }

    /**
     * Delete the values in complex and translated attributes.
     *
     * @param string[] $idList The list of item ids to remove.
     *
     * @return void
     */
    private function deleteAttributeValues(array $idList)
    {
        $languages = null;
        if ($this->metaModel->isTranslated()) {
            $languages = $this->metaModel->getAvailableLanguages();
        }
        foreach ($this->metaModel->getAttributes() as $attribute) {
            if ($attribute instanceof IComplex) {
                /** @var IComplex $attribute */
                $attribute->unsetDataFor($idList);
                continue;
            }
            if ($attribute instanceof ITranslated) {
                foreach ($languages as $language) {
                    $attribute->unsetValueFor($idList, $language);
                }
                continue;
            }
        }
    }

    /**
     * Split the attributes into variant and invariant ones and filter out all that do not need to get updated.
     *
     * @param IItem $item           The item to save.
     * @param bool  $baseAttributes If also the base attributes get updated as well.
     *
     * @return array
     */
    private function splitAttributes(IItem $item, $baseAttributes)
    {
        $variant   = [];
        $invariant = [];
        foreach ($this->metaModel->getAttributes() as $attributeName => $attribute) {
            // Skip unset attributes.
            if (!$item->isAttributeSet($attribute->getColName())) {
                continue;
            }
            if ($this->metaModel->hasVariants()) {
                if ($attribute->get('isvariant')) {
                    $variant[$attributeName] = $attribute;
                    continue;
                }
                if (!$baseAttributes && $item->isVariant()) {
                    // Skip base attribute.
                    continue;
                }
            }
            $invariant[$attributeName] = $attribute;
        }

        return [$variant, $invariant];
    }
}
