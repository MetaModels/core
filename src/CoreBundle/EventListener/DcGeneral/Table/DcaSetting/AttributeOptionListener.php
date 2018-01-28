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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * This handles retrieving attributes as options.
 */
class AttributeOptionListener extends AbstractListener
{
    /**
     * Retrieve the options for the attributes.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getAttributeOptions(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event) || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);

        if (!$metaModel) {
            return;
        }

        $options = [];

        // Fetch all attributes that exist in other settings.
        $alreadyTaken = $this->connection->createQueryBuilder()
            ->select('attr_id')
            ->from('tl_metamodel_dcasetting')
            ->where('dcatype="attribute"')
            ->andWhere('pid=:pid')
            ->setParameter('pid', $model->getProperty('pid'));

        // If an attribute is selected, we want to keep it in the list.
        if (!empty($attributeId = $model->getProperty('attr_id'))) {
            $alreadyTaken
                ->andWhere('attr_id<>:id')
                ->setParameter('id', $attributeId);
        }
        $alreadyTaken = $alreadyTaken->execute()->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($metaModel->getAttributes() as $attribute) {
            if (in_array($attribute->get('id'), $alreadyTaken)) {
                continue;
            }
            $options[$attribute->get('id')] = sprintf(
                '%s [%s]',
                $attribute->getName(),
                $attribute->get('type')
            );
        }

        $event->setOptions($options);
    }
}
