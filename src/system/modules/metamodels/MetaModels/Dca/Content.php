<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use Database\Result;
use MetaModels\Filter\Setting\Factory as FilterFactory;
use MetaModels\Factory as MetaModelFactory;

/**
 * Provides backend functionality.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Content
{
	/**
	 * Called from tl_content.onload_callback.
	 *
	 * @param \DC_Table $objDC The data container calling this method.
	 *
	 * @return void
	 */
	public function buildCustomFilter(\DC_Table $objDC)
	{
		$objContent = \Database::getInstance()
			->prepare(
				'SELECT	c.metamodel_filtering
				FROM	tl_content AS c
				JOIN	tl_metamodel AS mm ON mm.id = c.metamodel
				WHERE	c.id = ?
				AND		c.type = ?'
			)
			->limit(1)
			->execute($objDC->id, 'metamodel_content');

		if (!$objContent->metamodel_filtering)
		{
			unset($GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_filterparams']);
			return;
		}

		$objFilterSettings = FilterFactory::byId($objContent->metamodel_filtering);

		$GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_filterparams']['eval']['subfields'] =
			$objFilterSettings->getParameterDCA();
	}

	/**
	 * Fetch the template group for the current MetaModel content element.
	 *
	 * @param \DC_Table $objDC The data container calling this method.
	 *
	 * @return array
	 */
	public function getModuleTemplates(\DC_Table $objDC)
	{
		return Helper::getTemplatesForBase('ce_metamodel_' . $objDC->activeRecord->type);
	}

	/**
	 * Get frontend templates for filters.
	 *
	 * @return array
	 */
	public function getFilterTemplates()
	{
		return Helper::getTemplatesForBase('mm_filter_');
	}

	/**
	 * Fetch all attribute names for the current MetaModel.
	 *
	 * @param \DC_Table $objDc The data container calling this method.
	 *
	 * @return string[string] array of all attributes as colName => human name
	 */
	public function getAttributeNames(\DC_Table $objDc)
	{
		$arrAttributeNames = array('sorting' => $GLOBALS['TL_LANG']['MSC']['sorting']);
		$objMetaModel      = MetaModelFactory::byId($objDc->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
			}
		}

		return $arrAttributeNames;
	}

	/**
	 * Get attributes for checkbox wizard.
	 *
	 * @param Result $objRow The current row.
	 *
	 * @return array
	 */
	public function getFilterParameterNames(Result $objRow)
	{
		$return = array();

		if (!$objRow->activeRecord->metamodel_filtering)
		{
			return $return;
		}

		$objFilterSetting = FilterFactory::byId($objRow->activeRecord->metamodel_filtering);
		$arrParameterDca  = $objFilterSetting->getParameterFilterNames();

		return $arrParameterDca;
	}

	/**
	 * Return the edit wizard.
	 *
	 * @param \DC_Table $dc The data container.
	 *
	 * @return string
	 */
	public function editMetaModel(\DC_Table $dc)
	{
		if ($dc->value < 1)
		{
			return '';
		}

		$event = new GenerateHtmlEvent(
			'alias.gif',
			$GLOBALS['TL_LANG']['tl_content']['editmetamodel'][0],
			'style="vertical-align:top"'
		);

		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];

		$dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

		return sprintf(
			'<a href="contao/main.php?%s&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editmetamodel'][1]), $dc->value),
			$event->getHtml()
		);
	}

	/**
	 * Return the edit wizard.
	 *
	 * @param \DC_Table $dc The data container.
	 *
	 * @return string
	 */
	public function editFilterSetting(\DC_Table $dc)
	{
		if ($dc->value < 1)
		{
			return '';
		}

		$event = new GenerateHtmlEvent(
			'alias.gif',
			$GLOBALS['TL_LANG']['tl_content']['editfiltersetting'][0],
			'style="vertical-align:top"'
		);

		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];

		$dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

		return sprintf(
			'<a href="contao/main.php?%s&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels&table=tl_metamodel_filtersetting',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editfiltersetting'][1]), $dc->value),
			$event->getHtml()
		);
	}

	/**
	 * Return the edit wizard.
	 *
	 * @param \DC_Table $dc The data container.
	 *
	 * @return string
	 */
	public function editRenderSetting(\DC_Table $dc)
	{
		if ($dc->value < 1)
		{
			return '';
		}

		$event = new GenerateHtmlEvent(
			'alias.gif',
			$GLOBALS['TL_LANG']['tl_content']['editrendersetting'][0],
			'style="vertical-align:top"'
		);

		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];

		$dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

		return sprintf(
			'<a href="contao/main.php?%s&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels&table=tl_metamodel_rendersetting',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editrendersetting'][1]), $dc->value),
			$event->getHtml()
		);
	}

	/**
	 * Fetch all available filter settings for the current meta model.
	 *
	 * @param \DC_Table $objDC The data container calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getFilterSettings(\DC_Table $objDC)
	{
		$objDB             = \Database::getInstance();
		$objFilterSettings = $objDB
			->prepare('SELECT * FROM tl_metamodel_filter WHERE pid=?')
			->execute($objDC->activeRecord->metamodel);
		$arrSettings       = array();

		while ($objFilterSettings->next())
		{
			$arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
		}

		// Sort the filter settings.
		asort($arrSettings);

		return $arrSettings;
	}

	/**
	 * Fetch all available render settings for the current meta model.
	 *
	 * @param \DC_Table $objDC The data container calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getRenderSettings(\DC_Table $objDC)
	{
		$objFilterSettings = \Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=?')
			->execute($objDC->activeRecord->metamodel);

		$arrSettings = array();
		while ($objFilterSettings->next())
		{
			$arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
		}

		// Sort the render settings.
		asort($arrSettings);
		return $arrSettings;
	}

	/**
	 * Get a list with all allowed attributes for meta title.
	 * 
	 * @param \DC_Table $objDC The data container calling this method.
	 * 
	 * @return array A list with all found attributes.
	 */
	public function getMetaTitleAttributes(\DC_Table $objDC)
	{
		return Helper::getAttributeNamesForModel(
				$objDC->activeRecord->metamodel,
				(array)$GLOBALS['METAMODELS']['metainformation']['allowedTitle']
			);
	}

	/**
	 * Get a list with all allowed attributes for meta description.
	 * 
	 * @param \DC_Table $objDC The data container calling this method.
	 * 
	 * @return array A list with all found attributes.
	 */
	public function getMetaDescriptionAttributes(\DC_Table $objDC)
	{
		return Helper::getAttributeNamesForModel(
				$objDC->activeRecord->metamodel,
				(array)$GLOBALS['METAMODELS']['metainformation']['allowedDescription']
			);
	}
}
