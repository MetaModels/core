<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IInternal;
use MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter;
use MetaModels\CoreBundle\Sorter\AttributeSorter;
use MetaModels\IFactory;

/**
 * This provides the attribute name options.
 */
class AttributeOptionsListener extends AbstractListener
{
    /**
     * The attribute select option label formatter.
     *
     * @var SelectAttributeOptionLabelFormatter
     */
    private SelectAttributeOptionLabelFormatter $attributeLabelFormatter;

    /**
     * The attribute sorter.
     *
     * @var AttributeSorter
     */
    private AttributeSorter $attributeSorter;

    /**
     * {@inheritDoc}
     *
     * @param SelectAttributeOptionLabelFormatter $attributeLabelFormatter The attribute select option label formatter.
     * @param AttributeSorter                     $attributeSorter         The attribute sorter.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        SelectAttributeOptionLabelFormatter $attributeLabelFormatter,
        AttributeSorter $attributeSorter
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->attributeLabelFormatter = $attributeLabelFormatter;
        $this->attributeSorter         = $attributeSorter;
    }

    /**
     * Provide options for attribute type selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $result     = [];
        $metaModel  = $this->getMetaModelFromModel($event->getModel());
        $attributes = $metaModel->getAttributes();
        $attributes = $this->attributeSorter->sortByName($attributes);

        foreach ($attributes as $attribute) {
            if ($attribute instanceof IInternal) {
                continue;
            }
            $result[$attribute->get('id')] = $this->attributeLabelFormatter->formatLabel($attribute);
        }

        $event->setOptions($result);
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
        /** @var GetPropertyOptionsEvent $event */
        return parent::wantToHandle($event)
            && in_array($event->getPropertyName(), ['rendergroupattr', 'rendersortattr']);
    }
}
