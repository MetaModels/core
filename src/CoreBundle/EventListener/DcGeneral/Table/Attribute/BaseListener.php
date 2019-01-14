<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;
use MetaModels\IMetaModel;

/**
 * This trait provides a way to obtain a MetaModel.
 */
class BaseListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * The attribute factory.
     *
     * @var IAttributeFactory
     */
    protected $attributeFactory;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The MetaModel factory.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory
    ) {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->attributeFactory  = $attributeFactory;
        $this->factory           = $factory;
    }

    /**
     * Get the MetaModel instance referenced in the pid property of the Model.
     *
     * @param ModelInterface $model The model.
     *
     * @return IMetaModel
     *
     * @throws \InvalidArgumentException When the MetaModel could not be retrieved.
     */
    protected function getMetaModelByModelPid(ModelInterface $model)
    {
        $metaModel = $this
            ->factory
            ->getMetaModel(
                $this->factory->translateIdToMetaModelName($model->getProperty('pid'))
            );

        if ($metaModel === null) {
            throw new \InvalidArgumentException('Could not retrieve MetaModel ' . $model->getProperty('pid'));
        }

        return $metaModel;
    }

    /**
     * Create an attribute from the passed data.
     *
     * @param ModelInterface|null $model The information.
     *
     * @return IAttribute|null
     *
     * @throws \InvalidArgumentException When the MetaModel could not be retrieved.
     */
    protected function createAttributeInstance(ModelInterface $model = null)
    {
        if (null === $model) {
            return null;
        }

        return $this->attributeFactory->createAttribute(
            $model->getPropertiesAsArray(),
            $this->getMetaModelByModelPid($model)
        );
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel_attribute' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
