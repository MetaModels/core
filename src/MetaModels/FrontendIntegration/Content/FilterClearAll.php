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
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Content;

use MetaModels\FrontendIntegration\HybridFilterClearAll;

/**
 * Content element clearing the FE-filter.
 *
 * @package    MetaModels
 * @subpackage FrontendClearAll
 * @author     Stefan Heimes <cms@men-at-work.de>
 */
class FilterClearAll extends HybridFilterClearAll
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_filter_clearall';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $wildCardLink = 'contao/main.php?do=themes&amp;table=tl_content&amp;act=edit&amp;id=%s';

    /**
     * The link to use in the wildcard.
     *
     * @var string
     */
    protected $typePrefix = 'ce_';
}
