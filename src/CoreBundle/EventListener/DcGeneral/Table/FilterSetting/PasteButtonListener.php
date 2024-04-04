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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This class takes care of enabling and disabling of the paste button.
 */
class PasteButtonListener
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

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
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPasteButtonEvent $event)
    {
        $model = $event->getModel();
        assert($model instanceof ModelInterface);

        if (('tl_metamodel_filtersetting' !== $model->getProviderName())) {
            return;
        }

        $clipboard = $event->getEnvironment()->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $filter = Filter::create()->andModelIs(ModelId::fromModel($model))->andActionIs(ItemInterface::CUT);
        // Disable all buttons if there is a circular reference.
        if ($event->isCircularReference() || !$clipboard->isEmpty($filter)) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }
        $factory = $this->filterFactory->getTypeFactory($model->getProperty('type'));

        // If setting does not support children, omit them.
        if ($model->getId() && !($factory && $factory->isNestedType())) {
            $event->setPasteIntoDisabled(true);
        }
    }
}
