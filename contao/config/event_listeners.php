<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Events\MetaModel\CreateVariantButton;
use MetaModels\DcGeneral\Events\MetaModel\CutButton;
use MetaModels\DcGeneral\Events\MetaModel\DuplicateModel;
use MetaModels\DcGeneral\Events\MetaModel\PasteButton;
use MetaModels\DcGeneral\Events\Table\FilterSetting\FilterSettingTypeRendererCore;
use MetaModels\DcGeneral\Events\Table\InputScreens\InputScreenAddAllHandler;
use MetaModels\DcGeneral\Events\Table\RenderSetting\RenderSettingAddAllHandler;
use MetaModels\Events\CreatePropertyConditionEvent;
use MetaModels\Events\DatabaseBackedListener;
use MetaModels\Events\DefaultPropertyConditionCreator;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Filter\Setting\ConditionAndFilterSettingTypeFactory;
use MetaModels\Filter\Setting\ConditionOrFilterSettingTypeFactory;
use MetaModels\Filter\Setting\CustomSqlFilterSettingTypeFactory;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\Filter\Setting\SimpleLookupFilterSettingTypeFactory;
use MetaModels\Filter\Setting\StaticIdListFilterSettingTypeFactory;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use MultiColumnWizard\Event\GetOptionsEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

return array(
    MetaModelsEvents::SUBSYSTEM_BOOT => array(
        function (MetaModelsBootEvent $event) {
            /** @var EventDispatcherInterface $dispatcher */
            $dispatcher = func_get_arg(2);

            // This loads the factories.
            $handler = new DatabaseBackedListener();
            $handler->handleEvent($event);

            $dispatcher->addListener(
                CreatePropertyConditionEvent::NAME,
                array(new DefaultPropertyConditionCreator(), 'handle')
            );

            new DuplicateModel($event->getServiceContainer());
        }
    ),
    MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND => array(
        function (MetaModelsBootEvent $event) {
            $handler = new MetaModels\FrontendIntegration\Boot();
            $handler->perform($event);
        }
    ),
    MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND => array(
        function (MetaModelsBootEvent $event) {
            /** @var EventDispatcherInterface $dispatcher */
            $dispatcher = func_get_arg(2);

            $handler = new MetaModels\BackendIntegration\Boot();
            $handler->perform($event);
            new FilterSettingTypeRendererCore($event->getServiceContainer());
            new PasteButton($event->getServiceContainer());
            new CutButton($event->getServiceContainer());
            new CreateVariantButton($event->getServiceContainer());
            $dispatcher->addSubscriber(new RenderSettingAddAllHandler($event->getServiceContainer()));
            $dispatcher->addSubscriber(new InputScreenAddAllHandler($event->getServiceContainer()));
        }
    ),
    MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE => array(
        function (CreateFilterSettingFactoryEvent $event) {
            $factory = $event->getFactory();
            $factory
                ->addTypeFactory(new StaticIdListFilterSettingTypeFactory())
                ->addTypeFactory(new SimpleLookupFilterSettingTypeFactory())
                ->addTypeFactory(new CustomSqlFilterSettingTypeFactory())
                ->addTypeFactory(new ConditionAndFilterSettingTypeFactory())
                ->addTypeFactory(new ConditionOrFilterSettingTypeFactory());
        }
    ),
    GetOptionsEvent::NAME => array(
        function (GetOptionsEvent $event) {
            if (('tl_metamodel_dca' !== $event->getEnvironment()->getDataDefinition()->getName())
                || ('additionalFilters' !== $event->getPropertyName())
                || ('property' !== $event->getSubPropertyName())) {
                return;
            }

            /** @var IMetaModelsServiceContainer $serviceContainer */
            $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

            $metaModel = $serviceContainer->getFactory()->getMetaModel('mm_employee');

            $options = array_values(array_merge(
                $GLOBALS['METAMODELS_SYSTEM_COLUMNS'],
                array_map(
                    function (
                        /** @var IAttribute $attr */
                        $attr
                    ) {
                        return $attr->getColName();
                    },
                    $metaModel->getAttributes()
                )
            ));

            $event->setOptions($options);
        }
    ),
    BuildDataDefinitionEvent::NAME => array(
        [function (BuildDataDefinitionEvent $event) {
            $container = $event->getContainer();
            if (!$container instanceof IMetaModelDataDefinition) {
                return;
            }
            $definition  = $container->getBasicDefinition();
            $screenId    = $container->getMetaModelDefinition()->getActiveInputScreen();
            $dca = \Database::getInstance()
                ->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
                ->execute($screenId);

            if (empty($dca->additionalFilters)) {
                return;
            }

            // Fetch current filter
            $additionalFilter = $definition->getAdditionalFilter($definition->getDataProvider()) ?: [];
            $filterBuilder    = FilterBuilder::fromArrayForRoot($additionalFilter)->getFilter();

            $language = new ExpressionLanguage();
            $exprVars = ['user' => $GLOBALS['container']['user']];

            foreach (deserialize($dca->additionalFilters,true) as $f) {
                $value = $language->evaluate($f['expression'], $exprVars);
                $filterBuilder->andPropertyEquals($f['property'], $value);
            }

            $definition->setAdditionalFilter(
                $definition->getDataProvider(),
                $filterBuilder->getAllAsArray()
            );
        }, -500],
    ),
    // deprecated since 2.0, to be removed in 3.0.
    MetaModelsEvents::PARSE_ITEM => array(
        array(
            function (ParseItemEvent $event) {
                // HOOK: let third party extensions manipulate the generated data.
                if (empty($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
                    || !is_array($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
                ) {
                    return;
                }

                trigger_error(
                    'The HOOK MetaModelItem::parseValue has been replaced by the event ' .
                    MetaModelsEvents::PARSE_ITEM .
                    ' and will get removed in 3.0.',
                    E_USER_DEPRECATED
                );

                $result    = $event->getResult();
                $item      = $event->getItem();
                $format    = $event->getDesiredFormat();
                $settings  = $event->getRenderSettings();
                foreach ($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'] as $hook) {
                    $className = $hook[0];
                    $method    = $hook[1];

                    if (in_array('getInstance', get_class_methods($className))) {
                        $instance = call_user_func(array($className, 'getInstance'));
                    } else {
                        $instance = new $className();
                    }
                    $instance->$method($result, $item, $format, $settings);
                }

                $event->setResult($result);
            },
            -10
        )
    ),
    GetPropertyOptionsEvent::NAME => array(
        array(
            // Keep priority low to allow attributes like select and tags to override values.
            'MetaModels\DcGeneral\Events\MetaModel\PropertyOptionsProvider::getPropertyOptions',
            -200
        )
    )
);
