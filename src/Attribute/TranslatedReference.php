<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Frank Mueller <frank.mueller@linking-you.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use Contao\System;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

/**
 * This is the MetaModelAttribute class for handling translated attributes that reference another table.
 */
abstract class TranslatedReference extends BaseComplex implements ITranslated
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel $objMetaModel The MetaModel instance this attribute belongs to.
     * @param array      $arrData      The information array, for attribute information, refer to documentation of
     *                                 table tl_metamodel_attribute and documentation of the certain attribute classes
     *                                 for information what values are understood.
     * @param Connection $connection   Database connection.
     */
    public function __construct(IMetaModel $objMetaModel, $arrData = [], Connection $connection = null)
    {
        parent::__construct($objMetaModel, $arrData);

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        $this->connection = $connection;
    }

    /**
     * Retrieve the name of the table that contains the data for this reference.
     *
     * @return string
     */
    abstract protected function getValueTable();

    /**
     * Build a where clause for the given id(s) and language code.
     *
     * @param QueryBuilder         $queryBuilder The query builder for the query  being build.
     * @param string[]|string|null $mixIds       One, none or many ids to use.
     * @param string|string[]      $mixLangCode  The language code/s to use, optional.
     *
     * @return void
     */
    private function buildWhere(QueryBuilder $queryBuilder, $mixIds, $mixLangCode = '')
    {
        $alias = '';
        if (null !== $firstFromAlias = $queryBuilder->getQueryPart('from')[0]['alias'] ?? null) {
            $alias = $firstFromAlias . '.';
        }

        $queryBuilder
            ->andWhere($alias . 'att_id = :att_id')
            ->setParameter('att_id', $this->get('id'));

        if (!empty($mixIds)) {
            if (is_array($mixIds)) {
                $queryBuilder
                    ->andWhere($alias . 'item_id IN (:item_ids)')
                    ->setParameter('item_ids', $mixIds, Connection::PARAM_STR_ARRAY);
            } else {
                $queryBuilder
                    ->andWhere($alias . 'item_id = :item_id')
                    ->setParameter('item_id', $mixIds);
            }
        }

        if (!empty($mixLangCode)) {
            if (is_array($mixLangCode)) {
                $queryBuilder
                    ->andWhere($alias . 'langcode IN (:langcode)')
                    ->setParameter('langcode', $mixLangCode, Connection::PARAM_STR_ARRAY);
            } else {
                $queryBuilder
                    ->andWhere($alias . 'langcode = :langcode')
                    ->setParameter('langcode', $mixLangCode);
            }
        }
    }

    /**
     * Retrieve the values to be used in the INSERT or UPDATE SQL for the given parameters.
     *
     * @param array  $arrValue    The native value of the attribute.
     * @param int    $intId       The id of the item to be saved.
     * @param string $strLangCode The language code of the language the value is in.
     *
     * @return array
     *
     * @throws \InvalidArgumentException When the passed value is not null and not an array.
     */
    protected function getSetValues($arrValue, $intId, $strLangCode)
    {
        if (($arrValue !== null) && !is_array($arrValue)) {
            throw new \InvalidArgumentException(sprintf('Invalid value provided: %s', var_export($arrValue, true)));
        }

        return array
        (
            'tstamp' => time(),
            'value' => (string) $arrValue['value'],
            'att_id' => $this->get('id'),
            'langcode' => $strLangCode,
            'item_id' => $intId,
        );
    }

    /**
     * Retrieve the columns to be used for key and value when retrieving the filter options.
     *
     * Returned array must contain two elements having the keys "key" and "value".
     *
     * @return array
     */
    protected function getOptionizer()
    {
        return [
            'key'   => 'value',
            'value' => 'value'
        ];
    }


    /**
     * {@inheritDoc}
     */
    public function valueToWidget($varValue)
    {
        return $varValue['value'] ?? null;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function widgetToValue($varValue, $itemId)
    {
        return [
            'tstamp' => time(),
            'value'  => $varValue,
            'att_id' => $this->get('id'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDataFor($arrIds)
    {
        $strActiveLanguage   = $this->getActiveLanguage();
        $strFallbackLanguage = $this->getFallbackLanguage();

        $arrReturn = $this->getTranslatedDataFor($arrIds, $strActiveLanguage);

        // Second round, fetch fallback languages if not all items could be resolved.
        if ((count($arrReturn) < count($arrIds)) && ($strActiveLanguage != $strFallbackLanguage)) {
            $arrFallbackIds = array();
            foreach ($arrIds as $intId) {
                if (empty($arrReturn[$intId])) {
                    $arrFallbackIds[] = $intId;
                }
            }

            if ($arrFallbackIds) {
                $arrFallbackData = $this->getTranslatedDataFor($arrFallbackIds, $strFallbackLanguage);
                // Cannot use array_merge here as it would renumber the keys.
                foreach ($arrFallbackData as $intId => $arrValue) {
                    $arrReturn[$intId] = $arrValue;
                }
            }
        }
        return $arrReturn;
    }

    /**
     * Determine the available languages.
     *
     * @return null|\string[]
     *
     * @throws \RuntimeException When an untranslated MetaModel is encountered.
     */
    private function determineLanguages()
    {
        $metaModel = $this->getMetaModel();
        if ($metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getLanguages();
        }

        $languages = $this->getMetaModel()->getAvailableLanguages();
        if ($languages === null) {
            throw new \RuntimeException(
                'MetaModel ' . $this->getMetaModel()->getName() . ' does not seem to be translated.'
            );
        }
        return $languages;
    }

    /**
     * {@inheritDoc}
     */
    public function setDataFor($arrValues)
    {
        foreach ($this->determineLanguages() as $strLangCode) {
            $this->setTranslatedDataFor($arrValues, $strLangCode);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unsetDataFor($arrIds)
    {
        foreach ($this->determineLanguages() as $strLangCode) {
            $this->unsetValueFor($arrIds, $strLangCode);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function searchFor($strPattern)
    {
        return $this->searchForInLanguages($strPattern, array($this->getActiveLanguage()));
    }

    /**
     * {@inheritDoc}
     */
    public function searchForInLanguages($strPattern, $arrLanguages = array())
    {
        $optionizer = $this->getOptionizer();
        $procedure  = 't.' . $optionizer['value'] . ' LIKE :pattern';
        $strPattern = str_replace(['*', '?'], ['%', '_'], $strPattern);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('DISTINCT t.item_id')
            ->from($this->getValueTable(), 't')
            ->andWhere($procedure)
            ->setParameter('pattern', $strPattern);

        $this->buildWhere($queryBuilder, null, $arrLanguages);

        $filterRule = SimpleQuery::createFromQueryBuilder($queryBuilder, 'item_id');

        return $filterRule->getMatchingIds();
    }

    /**
     * {@inheritDoc}
     */
    public function sortIds($idList, $strDirection)
    {
        $builder = $this->connection->createQueryBuilder();
        $expr    = $builder->expr();
        $builder
            ->select('IF(t2.item_id IS NOT NULL, t2.item_id, t1.item_id)')
            ->from($this->getValueTable(), 't1')
            ->leftJoin(
                't1',
                $this->getValueTable(),
                't2',
                $expr
                    ->andX()
                    ->add($expr->eq('t1.att_id', 't2.att_id'))
                    ->add($expr->eq('t1.item_id', 't2.item_id'))
                    ->add($expr->eq('t2.langcode', ':langcode'))
            )
            ->where($expr->eq('t1.att_id', ':att_id'))
            ->andWhere($expr->in('t1.item_id', ':id_list'))
            ->andWhere($expr->in('t1.langcode', ':langfallbackcode'))
            ->setParameter('langcode', $this->getActiveLanguage())
            ->setParameter('langfallbackcode', $this->getFallbackLanguage())
            ->setParameter('att_id', $this->get('id'))
            ->setParameter('id_list', \array_unique($idList), ArrayParameterType::STRING);

        return $builder->executeQuery()->fetchFirstColumn();
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from($this->getValueTable(), 't');

        $this->buildWhere($queryBuilder, $idList, $this->getActiveLanguage());

        $statement     = $queryBuilder->executeQuery();
        $arrOptionizer = $this->getOptionizer();

        $arrReturn = [];
        while ($objValue = $statement->fetchAssociative()) {
            $arrReturn[$objValue[$arrOptionizer['key']]] = $objValue[$arrOptionizer['value']];
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslatedDataFor($arrValues, $strLangCode)
    {
        // First off determine those to be updated and those to be inserted.
        $arrIds      = array_keys($arrValues);
        $arrExisting = $this->fetchExistingIdsFor($arrIds, $strLangCode);
        $arrNewIds   = array_diff($arrIds, $arrExisting);

        // Update existing values - delete if empty.
        foreach ($arrExisting as $intId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $this->buildWhere($queryBuilder, $intId, $strLangCode);

            if ($arrValues[$intId]['value'] != '') {
                $queryBuilder->update($this->getValueTable(), 't');

                foreach ($this->getSetValues($arrValues[$intId], $intId, $strLangCode) as $name => $value) {
                    $queryBuilder
                        ->set('t.' . $name, ':' . $name)
                        ->setParameter($name, $value);
                }
            } else {
                $queryBuilder->delete($this->getValueTable());
            }

            $queryBuilder->executeQuery();
        }

        // Insert the new values.
        foreach ($arrNewIds as $intId) {
            if (!isset($arrValues[$intId]['value']) || $arrValues[$intId]['value'] == '') {
                continue;
            }

            $this->connection->insert(
                $this->getValueTable(),
                $this->getSetValues($arrValues[$intId], $intId, $strLangCode)
            );
        }
    }

    /**
     * Filter the item ids for ids that exist in the database.
     *
     * @param array  $idList   The id list.
     * @param string $langCode The language code.
     *
     * @return string[]
     */
    protected function fetchExistingIdsFor($idList, $langCode)
    {
        $queryBuilder = $this
            ->connection
            ->createQueryBuilder()
            ->select('t.item_id')
            ->from($this->getValueTable(), 't');
        $this->buildWhere($queryBuilder, $idList, $langCode);

        $statement = $queryBuilder->executeQuery();

        return $statement->fetchFirstColumn();
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslatedDataFor($arrIds, $strLangCode)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from($this->getValueTable(), 't');

        $this->buildWhere($queryBuilder, $arrIds, $strLangCode);

        $statement = $queryBuilder->executeQuery();
        $arrReturn = [];
        while ($value = $statement->fetchAssociative()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $arrReturn[$value['item_id']] = $value;
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetValueFor($arrIds, $strLangCode)
    {
        $queryBuilder = $this->connection->createQueryBuilder()->delete($this->getValueTable());
        $this->buildWhere($queryBuilder, $arrIds, $strLangCode);

        $queryBuilder->executeQuery();
    }

    /**
     * Retrieve the current language of the MetaModel we are attached to.
     *
     * @return string
     */
    private function getActiveLanguage()
    {
        $metaModel = $this->getMetaModel();
        if (!$metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getActiveLanguage();
        }

        return $metaModel->getLanguage();
    }

    /**
     * Retrieve the main language of the MetaModel we are attached to.
     *
     * @return string
     */
    private function getFallbackLanguage()
    {
        $metaModel = $this->getMetaModel();
        if (!$metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getFallbackLanguage();
        }

        return $metaModel->getMainLanguage();
    }
}
