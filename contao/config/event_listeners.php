<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

use MetaModels\Attribute\Events\CreateAttributeFactoryEvent;
use MetaModels\DcGeneral\Events\MetaModel\CreateVariantButton;
use MetaModels\DcGeneral\Events\MetaModel\CutButton;
use MetaModels\DcGeneral\Events\MetaModel\DuplicateModel;
use MetaModels\DcGeneral\Events\MetaModel\PasteButton;
use MetaModels\DcGeneral\Events\Table\FilterSetting\FilterSettingTypeRendererCore;
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
use MetaModels\MetaModelsEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

return array(
    MetaModelsEvents::SUBSYSTEM_BOOT => array(
        function (MetaModelsBootEvent $event) {
            $handler = new DatabaseBackedListener();
            $handler->handleEvent($event);
        }
    ),
    MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND => array(
        function (MetaModelsBootEvent $event) {
            $handler = new MetaModels\FrontendIntegration\Boot();
            $handler->perform($event);
        }
    ),
    MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND => array(
        function (MetaModelsBootEvent $event, $eventName, EventDispatcherInterface $dispatcher) {
            $dispatcher->addListener(
                CreatePropertyConditionEvent::NAME,
                array(new DefaultPropertyConditionCreator(), 'handle')
            );

            $handler = new MetaModels\BackendIntegration\Boot();
            $handler->perform($event);
            new FilterSettingTypeRendererCore($event->getServiceContainer());
            new PasteButton($event->getServiceContainer());
            new CutButton($event->getServiceContainer());
            new CreateVariantButton($event->getServiceContainer());
            new DuplicateModel($event->getServiceContainer());
        }
    ),
    MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE => array(
        function (CreateAttributeFactoryEvent $event) {
            \MetaModels\Attribute\Events\LegacyListener::registerLegacyAttributeFactoryEvents($event);
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

            \MetaModels\Filter\Setting\Events\LegacyListener::registerLegacyFactories($event);
        }
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
);
