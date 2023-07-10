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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use Contao\Input;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;

/**
 * Implementation of the MetaModel Backend Module that allowing access to MetaModel configuration etc. Everything below
 * https://..../contao/metamodels?.... ends up here.
 *
 * @deprecated Not in use anymore since 2.3.
 */
class Module
{
    /**
     * The data container.
     *
     * @var DataContainerInterface
     */
    private DataContainerInterface $dataContainer;

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
        $GLOBALS['TL_CSS'][] = 'bundles/metamodelscore/css/style.css';
        $arrModule           = $GLOBALS['BE_MOD']['metamodels']['metamodels'];
        // Custom action (if key is not defined in config.php the default action will be called).
        if (Input::get('key') && isset($arrModule[Input::get('key')])) {
            Callbacks::call($arrModule[Input::get('key')], $this, $arrModule);
        }

        $act = Input::get('act');
        if (!\strlen($act)) {
            $act = 'showAll';
        }

        $controller = $this->dataContainer->getEnvironment()->getController();
        assert($controller instanceof ControllerInterface);

        return $controller->handle(new Action($act));
    }
}
