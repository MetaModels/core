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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Helper\LocaleUtil;
use MetaModels\IMetaModel;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting_condition.
 *
 * @psalm-suppress PropertyNotSetInConstructor
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
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return 'tl_metamodel_dcasetting_condition' === $dataDefinition->getName();
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

        $dcaSettingId = $elements->getId('tl_metamodel_dcasetting');
        $elements->push(
            StringUtil::ampersand($builder->getUrl()),
            \sprintf(
                $elements->getLabel('tl_metamodel_dcasetting_condition'),
                (null !== $dcaSettingId) ? $this->getConditionAttribute($dcaSettingId) : ''
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

        if ($setting->dcatype === 'attribute') {
            $attribute     = $this->getRow($setting->attr_id, 'tl_metamodel_attribute');
            $metaModelName = $this->factory->translateIdToMetaModelName($attribute->pid);
            $metaModel     = $this->factory->getMetaModel($metaModelName);
            assert($metaModel instanceof IMetaModel);
            $attribute = $metaModel->getAttributeById((int) $attribute->id);
            if ($attribute) {
                return $attribute->getName();
            }
        } else {
            $title = StringUtil::deserialize($setting->legendtitle, true);
            // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
            return ($title[LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'])] ?? current($title));
        }

        return 'unknown ' . $setting->dcatype;
    }
}
