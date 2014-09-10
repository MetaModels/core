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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter;

use MetaModels\IMetaModel;

/**
 * This is the MetaModel filter interface.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Filter implements IFilter
{

    /**
     * The corresponding MetaModel.
     *
     * @var string
     */
    protected $strMetaModel = '';

    /**
     * The contained filter rules.
     *
     * @var array
     */
    protected $arrFilterRules = array();

    /**
     * The cached result after this filter has been evaluated.
     *
     * @var int[]
     */
    protected $arrMatches = null;

    /**
     * Create a new filter instance.
     *
     * @param IMetaModel $objMetaModel The MetaModel this filter shall apply to.
     */
    public function __construct(IMetaModel $objMetaModel)
    {
        if ($objMetaModel) {
            $this->strMetaModel = $objMetaModel->getTableName();
        }
    }

    /**
     * Clone the filter rule list.
     *
     * @return void
     */
    public function __clone()
    {
        $this->arrMatches     = null;
        $arrOld               = $this->arrFilterRules;
        $this->arrFilterRules = array();
        foreach ($arrOld as $objFilterRule) {
            $this->addFilterRule(clone $objFilterRule);
        }
    }

    /**
     * Create a copy of this filter.
     *
     * @return Filter|IFilter
     */
    public function createCopy()
    {
        $objCopy = clone $this;
        return $objCopy;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterRule(IFilterRule $objFilterRule)
    {
        // Reset matches as they are most likely invalid now.
        $this->arrMatches = null;

        $this->arrFilterRules[] = $objFilterRule;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        if ($this->arrMatches !== null) {
            return $this->arrMatches;
        }

        $arrIds = null;
        foreach ($this->arrFilterRules as $objFilterRule) {
            /** @var IFilterRule $objFilterRule */
            $arrRuleIds = $objFilterRule->getMatchingIds();
            if ($arrRuleIds === null) {
                continue;
            }
            // The first rule determines the master ids.
            if ($arrIds === null) {
                $arrIds = $arrRuleIds;
            } else {
                // NOTE: all rules are implicitely "AND"-ed together.
                $arrIds = array_intersect($arrIds, $arrRuleIds);
                // When no ids are left any more, the result will stay empty, do not evaluate any further rules.
                if (count($arrIds) == 0) {
                    break;
                }
            }
        }
        $this->arrMatches = $arrIds;
        return $arrIds;
    }
}
