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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use Doctrine\DBAL\Connection;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\IFactory;

/**
 * This handles the providing of available templates.
 */
class TemplateOptionListener extends AbstractListener
{
    /**
     * The template list provider.
     *
     * @var TemplateList
     */
    private $templateList;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param TemplateList             $templateList      The template list provider.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        TemplateList $templateList
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->templateList = $templateList;
    }

    /**
     * Retrieve the options for the attributes.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);
        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if (!$attribute) {
            return;
        }

        $event->setOptions($this->templateList->getTemplatesForBase('mm_attr_' . $attribute->get('type')));
    }
}
