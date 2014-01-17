<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;

class PropertyTlClass
{
	public static function getWizard(ManipulateWidgetEvent $event)
	{
		if(version_compare(VERSION, '3.0', '<'))
		{
			$link = ' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_STYLE_PICKER" data-lightbox="files 768 80%%">%s</a>';
		}
		else
		{
			$link = ' <a href="javascript:Backend.openModalIframe({url:\'system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_STYLE_PICKER\',width:790,title:\'Stylepicker\'});">%s</a>';
		}

		$event->getWidget()->wizard = sprintf(
			$link,
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getProperty()->getName(),
			$event->getProperty()->getName(),
			$event->getModel()->getId(),
			BackendBindings::generateImage(
				'system/modules/metamodels/html/dca_wizard.png',
				$event->getEnvironment()->getTranslator()->translate('stylepicker', 'tl_metamodel_dcasetting'),
				'style="vertical-align:top;"'
			)
		);
	}
}
