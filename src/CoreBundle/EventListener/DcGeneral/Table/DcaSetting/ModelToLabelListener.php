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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\IFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractListener
{
    /**
     * @var IAttributeFactory
     */
    private $attributeFactory;

    /**
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IconBuilder              $iconBuilder       The icon builder.
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        IAttributeFactory $attributeFactory,
        IconBuilder $iconBuilder,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->attributeFactory = $attributeFactory;
        $this->iconBuilder      = $iconBuilder;
        $this->translator       = $translator;
    }

    /**
     * Render the html for the input screen condition.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function handle(ModelToLabelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model = $event->getModel();

        switch ($model->getProperty('dcatype')) {
            case 'attribute':
                $this->drawAttribute($event);
                break;

            case 'legend':
                $this->drawLegend($event);
                break;

            default:
                break;
        }
    }

    /**
     * Draw the input screen setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    private function drawAttribute(ModelToLabelEvent $event)
    {
        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);
        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $type  = $attribute->get('type');
            $image = $this->iconBuilder->getBackendIconImageTag(
                $this->attributeFactory->getIconForType($type),
                $type,
                '',
                'bundles/metamodelscore/images/icons/fields.png'
            );
            $name     = $attribute->getName();
            $colName  = $attribute->getColName();
            $isUnique = $attribute->get('isunique');
        } else {
            $type     = 'unknown ID: ' . $model->getProperty('attr_id');
            $image    = $this->iconBuilder->getBackendIconImageTag('bundles/metamodelscore/images/icons/fields.png');
            $name     = 'unknown attribute';
            $colName  = 'unknown column';
            $isUnique = false;
        }

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong><span class="mandatory">%s</span> <span class="tl_class">%s</span>
                </div>')
            ->setArgs([
                $model->getProperty('published') ? 'published' : 'unpublished',
                $colName,
                $type,
                $image,
                $name,
                // unique attributes are automatically mandatory
                $model->getProperty('mandatory') || $isUnique
                    ? ' ['. $this->trans('mandatory.0') . ']'
                    : '',
                $model->getProperty('tl_class') ? sprintf('[%s]', $model->getProperty('tl_class')) : ''
            ]);
    }

    /**
     * Draw a legend.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function drawLegend(ModelToLabelEvent $event)
    {
        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);
        if (is_array($legend = StringUtil::deserialize($model->getProperty('legendtitle')))) {
            foreach ([$metaModel->getActiveLanguage(), $metaModel->getFallbackLanguage()] as $language) {
                if (array_key_exists($language, $legend)) {
                    if (!empty($legend[$language])) {
                        $legend = $legend[$language];
                        break;
                    }
                }
            }
        }
        if (empty($legend)) {
            $legend = 'legend';
        }

        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong></div>
                <div class="dca_palette">%s%s</div>')
            ->setArgs([
                $model->getProperty('published') ? 'published' : 'unpublished',
                $this->trans('dcatypes.legend'),
                $legend,
                $model->getProperty('legendhide') ? ':hide' : ''
            ]);
    }

    /**
     * Translate a key.
     *
     * @param string $key The key to translate.
     *
     * @return string
     */
    private function trans($key)
    {
        return $this->translator->trans('tl_metamodel_dcasetting.' . $key, [], 'contao_tl_metamodel_dcasetting');
    }
}
