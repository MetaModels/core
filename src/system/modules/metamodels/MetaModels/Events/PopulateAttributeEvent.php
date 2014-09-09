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

namespace MetaModels\Events;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;

/**
 * This event is triggered for every attribute when the data container is being built.
 */
class PopulateAttributeEvent
    extends AbstractEnvironmentAwareEvent
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.populate.attribute';

    /**
     * The Attribute.
     *
     * @var IAttribute
     */
    protected $attribute;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * Create a new container aware event.
     *
     * @param IMetaModel           $metaModel   The MetaModel.
     *
     * @param IAttribute           $attribute   The attribute being built.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(IMetaModel $metaModel, IAttribute $attribute, EnvironmentInterface $environment)
    {
        parent::__construct($environment);

        $this->metaModel = $metaModel;
        $this->attribute = $attribute;
    }

    /**
     * Retrieve the attribute.
     *
     * @return IAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }
}
