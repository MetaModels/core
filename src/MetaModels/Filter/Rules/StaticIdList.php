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

namespace MetaModels\Filter\Rules;

use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModel filter interface.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class StaticIdList extends FilterRule
{

    /**
     * The static id list that shall be applied.
     *
     * @var int[]
     */
    protected $arrIds = array();

    /**
     * Create a new FilterRule instance.
     *
     * @param int[] $arrIds Static list of ids that shall be returned as matches.
     */
    public function __construct($arrIds)
    {
        parent::__construct(null);
        $this->arrIds = $arrIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        return $this->arrIds;
    }
}
