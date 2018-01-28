<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Frank Mueller <frank.mueller@linking-you.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\Filter\Rules\SimpleQuery;

/**
 * This is the MetaModelAttribute class for handling translated attributes that reference another table.
 */
abstract class TranslatedReference extends BaseComplex implements ITranslated
{
    /**
     * Retrieve the name of the table that contains the data for this reference.
     *
     * @return string
     */
    abstract protected function getValueTable();

    /**
     * Build a where clause for the given id(s) and language code.
     *
     * @param string[]|string|null $mixIds      One, none or many ids to use.
     *
     * @param string|string[]      $mixLangCode The language code/s to use, optional.
     *
     * @return array
     */
    private function getWhere($mixIds, $mixLangCode = '')
    {
        $procedure  = 'att_id=?';
        $parameters = array($this->get('id'));

        if (!empty($mixIds)) {
            if (is_array($mixIds)) {
                $procedure .= ' AND item_id IN (' . $this->parameterMask($mixIds) . ')';
                $parameters = array_merge($parameters, $mixIds);
            } else {
                $procedure   .= ' AND item_id=?';
                $parameters[] = $mixIds;
            }
        }

        if (!empty($mixLangCode)) {
            if (is_array($mixLangCode)) {
                $procedure .= ' AND langcode IN (' . $this->parameterMask($mixLangCode) . ')';
                $parameters = array_merge($parameters, $mixLangCode);
            } else {
                $procedure   .= ' AND langcode=?';
                $parameters[] = $mixLangCode;
            }
        }

        return array(
            'procedure' => $procedure,
            'params'    => $parameters
        );
    }

    /**
     * Retrieve the values to be used in the INSERT or UPDATE SQL for the given parameters.
     *
     * @param array  $arrValue    The native value of the attribute.
     *
     * @param int    $intId       The id of the item to be saved.
     *
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
        return array(
            'key' => 'value',
            'value' => 'value'
        );
    }


    /**
     * {@inheritDoc}
     */
    public function valueToWidget($varValue)
    {
        return $varValue['value'];
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function widgetToValue($varValue, $itemId)
    {
        return array
        (
            'tstamp' => time(),
            'value'  => $varValue,
            'att_id' => $this->get('id'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDataFor($arrIds)
    {
        $strActiveLanguage   = $this->getMetaModel()->getActiveLanguage();
        $strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

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
        return $this->searchForInLanguages($strPattern, array($this->getMetaModel()->getActiveLanguage()));
    }

    /**
     * {@inheritDoc}
     */
    public function searchForInLanguages($strPattern, $arrLanguages = array())
    {
        $optionizer = $this->getOptionizer();
        $procedure  = $optionizer['value'] . ' LIKE ?';
        $parameters = array(str_replace(array('*', '?'), array('%', '_'), $strPattern));
        $arrWhere   = $this->getWhere(null, $arrLanguages);

        if ($arrWhere) {
            $procedure .= ' AND ' . $arrWhere['procedure'];
            $parameters = array_merge($parameters, $arrWhere['params']);
        }

        $filterRule = new SimpleQuery(
            sprintf('SELECT DISTINCT %1$s FROM %2$s WHERE %3$s', 'item_id', $this->getValueTable(), $procedure),
            $parameters,
            'item_id',
            $this->getMetaModel()->getServiceContainer()->getDatabase()
        );

        return $filterRule->getMatchingIds();
    }

    /**
     * {@inheritDoc}
     */
    public function sortIds($idList, $strDirection)
    {
        $objDB    = $this->getMetaModel()->getServiceContainer()->getDatabase();
        $langSet  = sprintf(
            '\'%s\',\'%s\'',
            $this->getMetaModel()->getActiveLanguage(),
            $this->getMetaModel()->getFallbackLanguage()
        );
        $objValue = $objDB->prepare(
            sprintf(
                'SELECT t1.item_id
                   FROM %1$s AS t1
                   RIGHT JOIN %1$s ON (t1.id = (SELECT
                       t2.id
                       FROM %1$s AS t2
                       WHERE (t2.att_id=%2$s)
                       AND langcode IN (%3$s)
                       AND (t2.item_id=t1.item_id)
                       ORDER BY FIELD(t2.langcode,%3$s)
                       LIMIT 1
                   ))
                   WHERE t1.id IS NOT NULL
                   AND  (t1.item_id IN (%4$s))
                   GROUP BY t1.id
                   ORDER BY t1.value %5$s',
                // @codingStandardsIgnoreStart - we want to keep the numbers at the end of the lines below.
                $this->getValueTable(),                    // 1
                $this->get('id'),                          // 2
                $langSet,                                  // 3
                $this->parameterMask($idList),             // 4
                $strDirection                              // 5
                // @codingStandardsIgnoreEnd
            )
        )
        ->execute($idList);

        return $objValue->fetchEach('item_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $objDB = $this->getMetaModel()->getServiceContainer()->getDatabase();
        // TODO: implement $arrIds and $usedOnly handling here.
        $arrWhere = $this->getWhere($idList, $this->getMetaModel()->getActiveLanguage());
        $strQuery = 'SELECT * FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

        $objValue = $objDB->prepare($strQuery)
            ->execute(($arrWhere ? $arrWhere['params'] : null));

        $arrOptionizer = $this->getOptionizer();

        $arrReturn = array();
        while ($objValue->next()) {
            $arrReturn[$objValue->{$arrOptionizer['key']}] = $objValue->{$arrOptionizer['value']};
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslatedDataFor($arrValues, $strLangCode)
    {
        $objDB = $this->getMetaModel()->getServiceContainer()->getDatabase();
        // First off determine those to be updated and those to be inserted.
        $arrIds      = array_keys($arrValues);
        $arrExisting = array_keys($this->getTranslatedDataFor($arrIds, $strLangCode));
        $arrNewIds   = array_diff($arrIds, $arrExisting);

        // Update existing values - delete if empty.
        $strQueryUpdate = 'UPDATE ' . $this->getValueTable() . ' %s';
        $strQueryDelete = 'DELETE FROM ' . $this->getValueTable();

        foreach ($arrExisting as $intId) {
            $arrWhere = $this->getWhere($intId, $strLangCode);

            if ($arrValues[$intId]['value'] != '') {
                $objDB->prepare($strQueryUpdate . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : ''))
                    ->set($this->getSetValues($arrValues[$intId], $intId, $strLangCode))
                    ->execute(($arrWhere ? $arrWhere['params'] : null));
            } else {
                $objDB->prepare($strQueryDelete . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : ''))
                    ->execute(($arrWhere ? $arrWhere['params'] : null));
            }
        }

        // Insert the new values.
        $strQueryInsert = 'INSERT INTO ' . $this->getValueTable() . ' %s';
        foreach ($arrNewIds as $intId) {
            if ($arrValues[$intId]['value'] == '') {
                continue;
            }
            $objDB->prepare($strQueryInsert)
                ->set($this->getSetValues($arrValues[$intId], $intId, $strLangCode))
                ->execute();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslatedDataFor($arrIds, $strLangCode)
    {
        $objDB = $this->getMetaModel()->getServiceContainer()->getDatabase();

        $arrWhere = $this->getWhere($arrIds, $strLangCode);
        $strQuery = 'SELECT * FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

        $objValue = $objDB->prepare($strQuery)
            ->execute(($arrWhere ? $arrWhere['params'] : null));

        $arrReturn = array();
        while ($objValue->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $arrReturn[$objValue->item_id] = $objValue->row();
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetValueFor($arrIds, $strLangCode)
    {
        $objDB = $this->getMetaModel()->getServiceContainer()->getDatabase();

        $arrWhere = $this->getWhere($arrIds, $strLangCode);
        $strQuery = 'DELETE FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

        $objDB->prepare($strQuery)
            ->execute(($arrWhere ? $arrWhere['params'] : null));
    }
}
