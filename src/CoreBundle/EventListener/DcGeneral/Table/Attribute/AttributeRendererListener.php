<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\ITranslatedMetaModel;

/**
 * This renders attribute information in the backend listing.
 */
class AttributeRendererListener extends BaseListener
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
            $translator = $event->getEnvironment()->getTranslator();
            assert($translator instanceof TranslatorInterface);

            /** @psalm-suppress InvalidArgument */
            $event
                ->setLabel(
                    '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                    <div class="field_type block">
                        <strong>%s</strong><br />
                    </div>'
                )
                ->setArgs([
                              $translator->translate('error_unknown_attribute.label', 'tl_metamodel_attribute'),
                              $type,
                              $translator->translate(
                                  'error_unknown_attribute.description',
                                  'tl_metamodel_attribute',
                                  ['%id%' => $type]
                              ),
                          ]);

            return;
        }

        $variant        = ($metaModel->hasVariants() && $attribute->get('isvariant')) ? ', variant' : '';
        $colName        = $attribute->getColName();
        $name           = $attribute->getName();
        $arrDescription = StringUtil::deserialize($attribute->get('description'));
        if (\is_array($arrDescription)) {
            $locale      = (string) System::getContainer()->get('request_stack')?->getCurrentRequest()?->getLocale();
            $description = $arrDescription[$locale] ?? null;
            /** @psalm-suppress DeprecatedMethod */
            if (null === $description) {
                if ($metaModel instanceof ITranslatedMetaModel) {
                    $description = $arrDescription[$metaModel->getMainLanguage()] ?? $attribute->getName();
                } else {
                    /** @psalm-suppress DeprecatedMethod */
                    $description = $arrDescription[(string) $attribute->getMetaModel()->getFallbackLanguage()]
                        ?? $attribute->getName();
                }
            }
        } else {
            $description = $arrDescription ?: $attribute->getName();
        }

        /** @psalm-suppress InvalidArgument */
        $event
            ->setLabel(
                '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s%s]</em></div>
                <div class="field_type block">
                    %s <strong>%s</strong> - %s
                </div>'
            )
            ->setArgs([
                $colName,
                $type,
                $variant,
                $image,
                $name,
                $description
            ]);
    }
}
