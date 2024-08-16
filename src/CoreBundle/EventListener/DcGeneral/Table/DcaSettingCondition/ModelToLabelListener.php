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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\IFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This handles the rendering of models to labels.
 */
class ModelToLabelListener extends AbstractListener
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private IconBuilder $iconBuilder;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param TranslatorInterface      $translator        The translator.
     * @param IconBuilder              $iconBuilder       The icon builder.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        TranslatorInterface $translator,
        IconBuilder $iconBuilder
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->translator  = $translator;
        $this->iconBuilder = $iconBuilder;
    }

    /**
     * Render the html for the input screen condition.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(ModelToLabelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $environment    = $event->getEnvironment();
        $model          = $event->getModel();
        $metaModel      = $this->getMetaModel($environment);
        $attribute      = $metaModel->getAttributeById((int) $model->getProperty('attr_id'));
        $type           = $model->getProperty('type');
        $parameterValue = (\is_array($model->getProperty('value'))
            ? \implode(', ', $model->getProperty('value'))
            : $model->getProperty('value')
        );

        $name = $this->translator->trans('conditionnames.' . $type, [], 'tl_metamodel_dcasetting_condition');

        $params = [
            '%icon%'      => $this->iconBuilder->getBackendIconImageTag(
                '/bundles/metamodelscore/images/icons/filter_default.png',
                $name,
                '',
                '/bundles/metamodelscore/images/icons/filter_default.png'
            ),
            '%name%'      => $name,
            '%attribute%' => $attribute ? $attribute->getName() : '' . $model->getProperty('attr_id'),
            '%value%'     => $parameterValue,
            '%comment%'   => '' !== ($comment = $model->getProperty('comment')) ? '<br/>' . $comment : '',
        ];

        /** @psalm-suppress InvalidArgument */
        $event
            ->setLabel($this->getLabelText($type, $params))
            ->setArgs(array_values($params));
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        $inputProvider = $event->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return $inputProvider->hasParameter('mode')
            ? parent::wantToHandle($event)
              && ('select' === $inputProvider->getParameter('act'))
            : parent::wantToHandle(
                $event
            );
    }

    /**
     * Retrieve the label text for a condition setting or the default one.
     *
     * @param string $type   The type of the element.
     * @param array  $params The params.
     *
     * @return string
     */
    private function getLabelText(string $type, array $params): string
    {
        $label = $this->translator->trans('typedesc.' . $type, $params, 'tl_metamodel_dcasetting_condition');

        if ($label === 'typedesc.' . $type) {
            $label = $this->translator->trans('typedesc._default_', $params, 'tl_metamodel_dcasetting_condition');
            if ($label === 'typedesc._default_') {
                return $type;
            }
        }

        return $label;
    }
}
