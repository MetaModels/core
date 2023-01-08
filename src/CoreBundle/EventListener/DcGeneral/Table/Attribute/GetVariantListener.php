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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;

/**
 * This class provides the attribute variant activation.
 */
class GetVariantListener extends BaseListener
{
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
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
    }

    /**
     * Set widget disabled/readonly if model not variant.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && 'isvariant' === $event->getProperty()->getName())) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModelByModelPid($model);

        if (!$metaModel->hasVariants()) {
            $extra             = $event->getProperty()->getExtra();
            $extra['disabled'] = true;
            $event->getProperty()->setExtra($extra);
            $model->setProperty('readonly', true);
        }
    }
}
