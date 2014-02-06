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

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\DC_General;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class RenderSettings extends Helper
{

	/**
	 * @var RenderSettings
	 */
	protected static $objInstance = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return RenderSettings
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null)
		{
			self::$objInstance = new RenderSettings();
		}
		return self::$objInstance;
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param \DCGeneral\DC_General $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getTemplates(DC_General $objDC)
	{
		return $this->getTemplatesForBase('metamodel_');
	}

	/**
	 * Get a list with all CSS files inside of the tl_files.
	 *
	 * @return array
	 */
	public function getCssFiles()
	{
		$arrCssFiles = array();

		$this->searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], $arrCssFiles, ".css");

		return $arrCssFiles;
	}

	/**
	 * Get a list with all JS files inside of the tl_files.
	 *
	 * @return array
	 */
	public function getJsFiles()
	{
		$arrJsFiles = array();

		$this->searchFiles($GLOBALS['TL_CONFIG']['uploadPath'], $arrJsFiles, ".js");

		return $arrJsFiles;
	}

	protected function searchFiles($strFolder, &$arrResult, $strExtension)
	{
		// Check if we have a file or folder.
		if(!is_file(TL_ROOT . '/' . $strFolder) && file_exists(TL_ROOT . '/' . $strFolder))
		{
			$arrScanResult = scan(TL_ROOT . '/' . $strFolder);
		}
		else if(is_file(TL_ROOT . '/' . $strFolder) && file_exists(TL_ROOT . '/' . $strFolder))
		{
			$arrScanResult = array();
		}

		// Run each value.
		foreach ($arrScanResult as $key => $value)
		{
			if(!is_file(TL_ROOT . '/' . $strFolder . '/' . $value))
			{
				$this->searchFiles($strFolder . '/' . $value, $arrResult, $strExtension);
			}
			else
			{
				if(preg_match('/'.$strExtension.'$/i', $value))
				{
					$arrResult[$strFolder][$strFolder . '/' . $value] = $value;
				}
			}
		}
	}

	/**
	 * Return the link picker wizard.
	 *
	 * @param DC_General $dc The DC_General currently in use.
	 *
	 * @return string
	 */
	public function pagePicker(DC_General $dc)
	{
		$environment = $dc->getEnvironment();

		if (version_compare(VERSION, '3.0', '<'))
		{
			$event = new GenerateHtmlEvent(
				'pickpage.gif',
				$environment->getTranslator()->translate('MSC.pagepicker'),
				'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' . $dc->inputName . '\')"'
			);
		}
		else
		{
			$url = sprintf('%scontao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_%s',
				\Environment::get('base'),
				$dc->inputName
			);

			$options = sprintf(
				"{'width':765,'title':'%s','url':'%s','id':'%s','tag':'ctrl_%s','self':this}",
				$environment->getTranslator()->translate('MOD.page.0'),
				$url,
				$dc->inputName,
				$dc->inputName
			);

			$event = new GenerateHtmlEvent(
				'pickpage.gif',
				$environment->getTranslator()->translate('MSC.pagepicker'),
				'style="vertical-align:top;cursor:pointer" onclick="Backend.openModalSelector(' . $options . ')"'
			);
		}

		$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

		return ' ' . $event->getHtml();
	}
}

