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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

/**
 * FE-module for FE-filtering.
 *
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class HybridFilterBlock extends MetaModelHybrid
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_filter_default';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        // Get template if configured.
        if ($this->metamodel_fef_template) {
            $this->strTemplate = $this->metamodel_fef_template;
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     *
     * @return void
     */
    protected function compile()
    {
        $objFilter = new FrontendFilter();
        $arrFilter = $objFilter->getMetaModelFrontendFilter($this);

        $this->Template->setData($arrFilter);
        $this->Template->submit = $arrFilter['submit'];
    }
}
