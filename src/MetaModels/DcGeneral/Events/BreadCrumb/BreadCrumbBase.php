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

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Base class for calculating hierarchical breadcrumbs.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
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
        // FIXME: need the environment here.
        return \Environment::getInstance()->base;
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

        return IdSerializer::fromSerialized($parameter)->getId();
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
        return IdSerializer::fromValues($dataProviderName, $modelId)->getSerialized();
    }

    /**
     * Generate an url from the given parameters.
     *
     * @param string $tableName The name of the table to link to.
     *
     * @param string $itemId    The id of the item in the given table.
     *
     * @return string The generated URL.
     */
    public function generateUrl($tableName, $itemId)
    {
        $urlEvent = new AddToUrlEvent(
            sprintf(
                'do=metamodels&table=%s&pid=%s',
                $tableName,
                $itemId
            )
        );

        $this->getServiceContainer()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        return $urlEvent->getUrl();
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
