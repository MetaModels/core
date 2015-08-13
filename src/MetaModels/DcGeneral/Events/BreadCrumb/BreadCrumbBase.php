<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Base class for calculating hierarchical breadcrumbs.
 */
abstract class BreadCrumbBase
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the database.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->getServiceContainer()->getDatabase();
    }

    /**
     * Get for a table the human readable name or a fallback.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param string               $table       Name of table.
     *
     * @return string The human readable name.
     */
    protected function getBreadcrumbLabel(EnvironmentInterface $environment, $table)
    {
        $shortTable = str_replace('tl_', '', $table);

        $label = $environment->getTranslator()->translate($shortTable, 'BRD');

        if ($label == $shortTable) {
            $shortTable = str_replace('tl_metamodel_', '', $table);
            return strtoupper(substr($shortTable, 0, 1)) . substr($shortTable, 1, (strlen($shortTable) - 1)) . ' %s';
        }

        return specialchars($label);
    }

    /**
     * Retrieve the current base url.
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        return \Environment::get('base');
    }

    /**
     * Check if the given table is the current table.
     *
     * @param string                 $table The name of the table.
     *
     * @param InputProviderInterface $input The input provider in use.
     *
     * @return bool
     */
    protected function isActiveTable($table, InputProviderInterface $input)
    {
        return $input->getParameter('table') == $table;
    }

    /**
     * Extract the id value from the serialized parameter with the given name.
     *
     * @param EnvironmentInterface $environment   The environment.
     *
     * @param string               $parameterName The parameter name containing the id.
     *
     * @return int
     */
    protected function extractIdFrom(EnvironmentInterface $environment, $parameterName = 'pid')
    {
        $parameter = $environment->getInputProvider()->getParameter($parameterName);

        return ModelId::fromSerialized($parameter)->getId();
    }

    /**
     * Create an serialized id from the passed values.
     *
     * @param string $dataProviderName The data provider name.
     *
     * @param mixed  $modelId          The id.
     *
     * @return string
     */
    public function seralizeId($dataProviderName, $modelId)
    {
        return ModelId::fromValues($dataProviderName, $modelId)->getSerialized();
    }

    /**
     * Generate an url from the given parameters.
     *
     * @param string $tableName  The name of the table to link to.
     *
     * @param string $itemId     The id of the item in the given table.
     *
     * @param bool   $keepAction Flag if the "act" and "id" parameter shall be preserved in the URL.
     *
     * @return string The generated URL.
     */
    public function generateUrl($tableName, $itemId, $keepAction = false)
    {
        $urlEvent = new AddToUrlEvent(
            sprintf(
                'do=metamodels&table=%s&pid=%s',
                $tableName,
                $itemId
            )
        );

        $this->getServiceContainer()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        $builder = new UrlBuilder($urlEvent->getUrl());
        if (!$keepAction) {
            $builder->unsetQueryParameter('act');
            $builder->unsetQueryParameter('id');
        }

        return ampersand($builder->getUrl());
    }

    /**
     * Perform the bread crumb generating.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param array                $elements    The elements generated so far.
     *
     * @return array
     */
    abstract public function getBreadcrumbElements(EnvironmentInterface $environment, $elements);

    /**
     * Event handler.
     *
     * @param GetBreadcrumbEvent $event The event.
     *
     * @return void
     */
    public function getBreadcrumb(GetBreadcrumbEvent $event)
    {
        $environment = $event->getEnvironment();

        $event->setElements($this->getBreadcrumbElements($environment, array()));

        $event->stopPropagation();
    }
}
