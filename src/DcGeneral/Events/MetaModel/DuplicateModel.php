<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use MetaModels\IFactory;

/**
 * This class handles the paste into or after handling for variants.
 */
class DuplicateModel
{
    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the paste into and after event.
     *
     * @param PreDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PreDuplicateModelEvent $event)
    {
        $model = $event->getModel();

        $metaModel = $this->factory->getMetaModel($model->getProviderName());

        if (!$metaModel || !$metaModel->hasVariants()) {
            return;
        }

        // If we have a varbase, reset the vargroup because we got a new id.
        if ($model->getProperty('varbase') == 1) {
            $model->setProperty('vargroup', null);
        }
    }
}
