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
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;

/**
 * Implementation of the MetaModel Backend Module that allowing access to MetaModel configuration etc. Everything below
 * http://..../contao/main.php?do=metamodels&.... ends up here.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Module
{
    /**
     * The data container.
     *
     * @var DataContainerInterface
     */
    private $dataContainer;

    /**
     * Create a new instance.
     *
     * @param DataContainerInterface $dataContainer The data container.
     */
    public function __construct(DataContainerInterface $dataContainer)
    {
        $this->dataContainer = $dataContainer;
    }

    /**
     * Parse the template.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate()
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';
        $arrModule           = $GLOBALS['BE_MOD']['metamodels']['metamodels'];
        // Custom action (if key is not defined in config.php the default action will be called).
        if (\Input::get('key') && isset($arrModule[\Input::get('key')])) {
            Callbacks::call($arrModule[\Input::get('key')], $this, $arrModule);
        }

        $act = \Input::get('act');
        if (!strlen($act)) {
            $act = 'showAll';
        }

        return $this->dataContainer->getEnvironment()->getController()->handle(new Action($act));
    }
}
