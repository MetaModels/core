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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\DcGeneral\PropertyConditionFactory;
use MetaModels\IFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This handles the type options for conditions.
 */
class TypeOptionsListener extends AbstractConditionFactoryUsingListener
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
     * @param PropertyConditionFactory $conditionFactory  The condition factory.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        PropertyConditionFactory $conditionFactory,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection, $conditionFactory);
        $this->translator = $translator;
    }

    /**
     * Provide options for property condition types.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $options = [];
        foreach ($this->conditionFactory->getTypeNames() as $condition) {
            $options[$condition] = $this->translator->trans(
                'tl_metamodel_dcasetting_condition.conditionnames.' . $condition,
                [],
                'contao_tl_metamodel_dcasetting_condition'
            );
        }

        $event->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        /** @var GetPropertyOptionsEvent $event */
        return parent::wantToHandle($event) && ('type' === $event->getPropertyName());
    }
}
