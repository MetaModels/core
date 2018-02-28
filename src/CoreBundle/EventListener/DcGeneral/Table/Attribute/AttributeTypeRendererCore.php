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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * This renders attribute information in the backend listing.
 */
class AttributeTypeRendererCore extends BaseListener
{
    /**
     * Draw the attribute in the backend listing.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model     = $event->getModel();
        $type      = $model->getProperty('type');
        $image     = '<img src="' . $this->attributeFactory->getIconForType($type) . '" />';
        $metaModel = $this->getMetaModelByModelPid($model);

        $attribute = $this->attributeFactory->createAttribute($model->getPropertiesAsArray(), $metaModel);

        if (!$attribute) {
            $translator = $event
                ->getEnvironment()
                ->getTranslator();

            $event
                ->setLabel(
                    '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                    <div class="field_type block">
                        <strong>%s</strong><br />
                    </div>'
                )
                ->setArgs(
                    array
                    (
                        $translator->translate('error_unknown_attribute.0', 'tl_metamodel_attribute'),
                        $type,
                        $translator->translate('error_unknown_attribute.1', 'tl_metamodel_attribute', array($type)),
                    )
                );
            return;
        }

        $colName        = $attribute->getColName();
        $name           = $attribute->getName();
        $arrDescription = deserialize($attribute->get('description'));
        if (is_array($arrDescription)) {
            $description = $arrDescription[$attribute->getMetaModel()->getActiveLanguage()];
            if (!$description) {
                $description = $arrDescription[$attribute->getMetaModel()->getFallbackLanguage()];
            }
        } else {
            $description = $arrDescription ?: $attribute->getName();
        }

        $event
            ->setLabel(
                '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong> - %s
                </div>'
            )
            ->setArgs(array(
                $colName,
                $type,
                $image,
                $name,
                $description
            ));
    }
}
