<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting_condition.
 */
class BreadcrumbDcaSettingConditionListener extends AbstractBreadcrumbListener
{
    use ConnectionTrait;
    use GetMetaModelTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        return 'tl_metamodel_dcasetting_condition' === $event->getEnvironment()->getDataDefinition()->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if (!$elements->hasId('tl_metamodel_dcasetting')) {
            $elements->setId('tl_metamodel_dcasetting', $this->extractIdFrom($environment, 'pid'));
        }

        parent::getBreadcrumbElements($environment, $elements);

        $builder = UrlBuilder::fromUrl($elements->getUri())
            ->setQueryParameter('do', 'metamodels')
            ->setQueryParameter('table', 'tl_metamodel_dcasetting_condition')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues(
                    'tl_metamodel_dcasetting',
                    $elements->getId('tl_metamodel_dcasetting')
                )->getSerialized()
            )
            ->unsetQueryParameter('act')
            ->unsetQueryParameter('id');

        $elements->push(
            ampersand($builder->getUrl()),
            sprintf(
                $elements->getLabel('tl_metamodel_dcasetting_condition'),
                $this->getConditionAttribute($elements->getId('tl_metamodel_dcasetting'))
            ),
            'bundles/metamodelscore/images/icons/dca_condition.png'
        );
    }

    /**
     * Calculate the name of a sub palette attribute.
     *
     * @param string $settingId The id of the tl_metamodel_dcasetting.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getConditionAttribute($settingId)
    {
        $setting = $this->getRow($settingId, 'tl_metamodel_dcasetting');

        if ($setting->dcatype == 'attribute') {
            $attribute     = (object) $this->getRow($setting->attr_id, 'tl_metamodel_attribute');
            $metaModelName = $this->factory->translateIdToMetaModelName($attribute->pid);
            $attribute     = $this->factory->getMetaModel($metaModelName)->getAttributeById((int) $attribute->id);
            if ($attribute) {
                return $attribute->getName();
            }
        } else {
            $title = StringUtil::deserialize($setting->legendtitle, true);
            return $title[\str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])] ?? current($title);
        }

        return 'unknown ' . $setting->dcatype;
    }
}
