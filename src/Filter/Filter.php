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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

use MetaModels\IMetaModel;

/**
 * This is the MetaModel filter interface.
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
     * @var list<IFilterRule>
     */
    protected $arrFilterRules = [];

    /**
     * The cached result after this filter has been evaluated.
     *
     * @var list<string>|null
     */
    protected $arrMatches = null;

    /**
     * Create a new filter instance.
     *
     * @param IMetaModel $objMetaModel The MetaModel this filter shall apply to.
     */
    public function __construct(IMetaModel $objMetaModel)
    {
        $this->strMetaModel = $objMetaModel->getTableName();
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
        $this->arrFilterRules = [];
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
        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterRule(IFilterRule $objFilterRule)
    {
        // Reset matches as they are most likely invalid now.
        $this->arrMatches = null;

        $this->arrFilterRules[] = $objFilterRule;

        return $this;
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
            $arrRuleIds = $objFilterRule->getMatchingIds();
            if ($arrRuleIds === null) {
                continue;
            }
            // The first rule determines the master ids.
            if ($arrIds === null) {
                $arrIds = $arrRuleIds;
            } else {
                // NOTE: all rules are implicitely "AND"-ed together.
                $arrIds = \array_intersect($arrIds, $arrRuleIds);
                // When no ids are left anymore, the result will stay empty, do not evaluate any further rules.
                if (\count($arrIds) === 0) {
                    break;
                }
            }
        }
        $arrIds = (null !== $arrIds) ? \array_values($arrIds) : null;

        $this->arrMatches = $arrIds;

        return $arrIds;
    }
}
