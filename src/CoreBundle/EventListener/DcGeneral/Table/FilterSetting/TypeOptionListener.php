<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This class provides the type options.
 */
class TypeOptionListener
{
    /**
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     */
    public function __construct(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (('tl_metamodel_filtersetting' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('type' !== $event->getPropertyName())) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = [];
        foreach ($this->filterFactory->getTypeNames() as $filter) {
            $options[$filter] = $translator->translate('typenames.' . $filter, 'tl_metamodel_filtersetting');
        }

        $event->setOptions($options);
    }
}
