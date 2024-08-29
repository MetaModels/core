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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractListener
{
    /**
     * The attribute factory.
     *
     * @var IAttributeFactory
     */
    private IAttributeFactory $attributeFactory;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private IconBuilder $iconBuilder;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IconBuilder              $iconBuilder       The icon builder.
     * @param TranslatorInterface      $translator        The translator.
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

        $model     = $event->getModel();
        $metaModel = $this->getMetaModelFromModel($model);
        assert($metaModel instanceof IMetaModel);
        $attribute = $metaModel->getAttributeById((int) $model->getProperty('attr_id'));

        if ($attribute) {
            $type    = $attribute->get('type');
            $image   = $this->iconBuilder->getBackendIconImageTag(
                $this->attributeFactory->getIconForType($type),
                $type,
                '',
                '/bundles/metamodelscore/images/icons/fields.png'
            );
            $variant = ($metaModel->hasVariants() && $attribute->get('isvariant')) ? ', variant' : '';
            $name    = $attribute->getName();
            $colName = $attribute->getColName();
        } else {
            $type    = $this->trans('error_unknown_id', ['%id%' => $model->getProperty('attr_id')]);
            $image   = $this->iconBuilder->getBackendIconImageTag('/bundles/metamodelscore/images/icons/fields.png');
            $variant = '';
            $name    = $this->trans('error_unknown_attribute');
            $colName = $this->trans('error_unknown_column');
        }

        /** @psalm-suppress InvalidArgument */
        $event
            ->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s%s]</em></div>
                <div class="field_type block">
                    %s <strong>%s</strong>
                </div>')
            ->setArgs([
                $model->getProperty('enabled') ? 'published' : 'unpublished',
                $colName,
                $type,
                $variant,
                $image,
                $name
            ]);
    }

    /**
     * Translate a key.
     *
     * @param string $key    The key to translate.
     * @param array  $params The parameters.
     *
     * @return string
     */
    private function trans(string $key, array $params = []): string
    {
        return $this->translator->trans($key, $params, 'tl_metamodel_rendersettings');
    }
}
