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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * Draw a input screen element.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class ModelToLabel
{
    /**
     * Render the html for the input screen.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public static function render(ModelToLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        $translator  = $environment->getTranslator();
        $model       = $event->getModel();

        if (!$model->getProperty('isdefault')) {
            return;
        }

        $event->setLabel(
            sprintf(
                '%s <span style="color:#b3b3b3; padding-left:3px">[%s]</span>',
                $event->getLabel(),
                $translator->translate('MSC.fallback')
            )
        );
    }
}
