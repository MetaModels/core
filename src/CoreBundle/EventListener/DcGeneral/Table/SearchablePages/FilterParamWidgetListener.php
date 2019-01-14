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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This handles the providing of available filters.
 */
class FilterParamWidgetListener extends AbstractAbstainingListener
{
    /**
     * The filter factory.
     *
     * @var IFilterSettingFactory
     */
    private $settingFactory;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFilterSettingFactory    $settingFactory    The connection.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, IFilterSettingFactory $settingFactory)
    {
        parent::__construct($scopeDeterminator);
        $this->settingFactory = $settingFactory;
    }

    /**
     * Build the filter params for the widget.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getProperty()->getName() !== 'filterparams')) {
            return;
        }

        $model = $event->getModel();

        $objFilterSettings  = $this->settingFactory->createCollection($model->getProperty('filter'));
        $extra              = $event->getProperty()->getExtra();
        $extra['subfields'] = $objFilterSettings->getParameterDCA();
        $event->getProperty()->setExtra($extra);
    }
}
