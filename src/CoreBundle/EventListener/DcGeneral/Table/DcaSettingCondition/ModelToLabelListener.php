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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use Symfony\Component\Translation\TranslatorInterface;

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
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param Connection               $connection        The database connection.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection);
        $this->translator = $translator;
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

        // FIXME: we should only handle the core provided conditions here:
        // conditionor,
        // conditionand,
        // conditionpropertyvalueis,
        // conditionpropertycontainanyof,
        // conditionpropertyvisible,
        // conditionnot

        $environment    = $event->getEnvironment();
        $model          = $event->getModel();
        $metaModel      = $this->getMetaModel($environment);
        $attribute      = $metaModel->getAttributeById($model->getProperty('attr_id'));
        $type           = $model->getProperty('type');
        $parameterValue = (is_array($model->getProperty('value'))
            ? implode(', ', $model->getProperty('value'))
            : $model->getProperty('value'));
        $name = $this->translator->trans(
            'tl_metamodel_dcasetting_condition.conditionnames.' . $type,
            [],
            'contao_tl_metamodel_dcasetting_condition'
        );

        $image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
        if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
            $image = 'bundles/metamodelscore/images/icons/filter_default.png';
        }

        /** @var GenerateHtmlEvent $imageEvent */
/*
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );
*/

        $event
            ->setLabel($this->getLabelText($type))
            ->setArgs([
                '', // $imageEvent->getHtml(),
                $name,
                $attribute ? $attribute->getName() : '' . $model->getProperty('attr_id'),
                $parameterValue
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        return parent::wantToHandle($event) || (
            $event->getEnvironment()->getInputProvider()->hasParameter('mode')
                && 'select' === $event->getEnvironment()->getInputProvider()->getParameter('act')
            );
    }

    /**
     * Retrieve the label text for a condition setting or the default one.
     *
     * @param string $type The type of the element.
     *
     * @return string
     */
    private function getLabelText($type)
    {
        $label = $this->translator->trans(
            'tl_metamodel_dcasetting_condition.typedesc.' . $type,
            [],
            'contao_tl_metamodel_dcasetting_condition'
        );
        if ($label == 'tl_metamodel_dcasetting_condition.typedesc.' . $type) {
            $label = $this->translator->trans(
                'tl_metamodel_dcasetting_condition.typedesc._default_',
                [],
                'contao_tl_metamodel_dcasetting_condition'
            );
            if ($label == 'tl_metamodel_dcasetting_condition.typedesc._default_') {
                return $type;
            }
        }
        return $label;
    }
}
