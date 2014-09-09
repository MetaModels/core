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

namespace MetaModels\Filter\Setting;

use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\Helper\ContaoController;
use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * Base class for filter setting implementation.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class Simple implements ISimple
{
	/**
	 * The parenting filter setting container this setting belongs to.
	 *
	 * @var \MetaModels\Filter\Setting\ICollection
	 */
	protected $objFilterSettings = null;

	/**
	 * The attributes of this filter setting.
	 *
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Constructor - initialize the object and store the parameters.
	 *
	 * @param \MetaModels\Filter\Setting\ICollection $objFilterSetting The parenting filter settings object.
	 *
	 * @param array                                  $arrData          The attributes for this filter setting.
	 */
	public function __construct($objFilterSetting, $arrData)
	{
		$this->objFilterSetting = $objFilterSetting;
		$this->arrData          = $arrData;
	}

	/**
	 * Return the value of the requested attribute.
	 *
	 * @param string $strKey Name of the attribute to retrieve.
	 *
	 * @return mixed The stored value, if any.
	 */
	public function get($strKey)
	{
		return $this->arrData[$strKey];
	}

	/**
	 * Get the parenting collection instance.
	 *
	 * @return \MetaModels\Filter\Setting\ICollection The parent.
	 */
	protected function getFilterSettings()
	{
		return $this->objFilterSetting;
	}

	/**
	 * Return the MetaModel instance this filter setting relates to.
	 *
	 * @return \MetaModels\IMetaModel
	 */
	protected function getMetaModel()
	{
		return $this->getFilterSettings()->getMetaModel();
	}

	/**
	 * Returns if the given value is currently active in the given filter settings.
	 *
	 * @param array  $arrWidget    The widget information.
	 *
	 * @param array  $arrFilterUrl The filter url parameters to use.
	 *
	 * @param string $strKeyOption The option value to determine.
	 *
	 * @return bool  true If the given value is mentioned in the given filter parameters, false otherwise.
	 */
	protected function isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		$blnIsActive = isset($arrFilterUrl[$arrWidget['eval']['urlparam']])
			&& ($arrFilterUrl[$arrWidget['eval']['urlparam']] == $strKeyOption);
		if (!$blnIsActive && $this->get('defaultid'))
		{
			$blnIsActive = ($arrFilterUrl[$arrWidget['eval']['urlparam']] == $this->get('defaultid'));
		}
		return $blnIsActive;
	}

	/**
	 * Translate an option to a proper url value to be used in the filter url.
	 *
	 * Overriding this method allows to toggle the value in the url in addition to extract
	 * or inject a value into an "combined" filter url parameter (like tags i.e.)
	 *
	 * @param array  $arrWidget    The widget information.
	 *
	 * @param array  $arrFilterUrl The filter url parameters to use.
	 *
	 * @param string $strKeyOption The option value to determine.
	 *
	 * @return string The filter url value to use for link gererating.
	 */
	protected function getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		// Toggle if active.
		if ($this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption))
		{
			return '';
		}
		return $strKeyOption;
	}

	/**
	 * Add a parameter to the url, if it is auto_item, it will get prepended.
	 *
	 * @param string $url    The url built so far.
	 *
	 * @param string $name   The parameter name.
	 *
	 * @param mixed  $value  The parameter value.
	 *
	 * @return string.
	 */
	protected function addUrlParameter($url, $name, $value)
	{
		if (is_array($value))
		{
			$value = implode(',', array_filter($value));
		}

		$value = str_replace('%', '%%', urlencode($value));

		if (empty($value))
		{
			return $url;
		}

		if ($name !== 'auto_item')
		{
			$url .= '/' . $name . '/' . $value;
		}
		else
		{
			$url = '/' . $value . $url;
		}

		return $url;
	}

	/**
	 * Build the filter url based upon the fragments.
	 *
	 * @param array  $fragments The parameters to be used in the Url.
	 *
	 * @param string $searchKey The param key to handle for "this".
	 *
	 * @return string
	 */
	protected function buildFilterUrl($fragments, $searchKey)
	{
		$url   = '';
		$found = false;

		// Create base url containing for preserving the current filter on unrelated widgets and modules.
		// The URL parameter concerning us will be masked via %s to be used later on in a sprintf().
		foreach ($fragments as $key => $value)
		{
			// Skip the magic "language" parameter.
			if (($key == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
			{
				continue;
			}

			if ($key == $searchKey)
			{
				if ($key !== 'auto_item')
				{
					$url .= '%s';
				}
				else
				{
					$url = '%s' . $url;
				}
				$found = true;
			}
			else
			{
				$url = $this->addUrlParameter($url, $key, $value);
			}
		}

		// If we have not found our parameter in the URL, we add it as %s now to be able to populate it via sprintf() below.
		if (!$found)
		{
			if ($searchKey !== 'auto_item')
			{
				$url .= '%s';
			}
			else
			{
				$url = '%s' . $url;
			}
		}

		return $url;
	}

	/**
	 * Generate the options for the frontend widget as the frontend templates expect them.
	 *
	 * The returning array will be made of option arrays containing the following fields:
	 * * key    The option value as raw key from the options array in the given widget information.
	 * * value  The value to show as option label.
	 * * href   The URL to use to activate this value in the filter.
	 * * active Boolean determining if this value is the current active option in the widget.
	 * * class  The CSS class to use. Contains active if the option is active or is empty otherwise.
	 *
	 * @param array $arrWidget     The widget information to use for value generating.
	 *
	 * @param array $arrFilterUrl  The filter url parameters to use.
	 *
	 * @param array $arrJumpTo     The jumpTo page to use for URL generating - if empty, the current
	 *                             frontend page will get used.
	 *
	 * @param bool  $blnAutoSubmit Determines if the generated options/widgets shall perform auto submitting
	 *                             or not.
	 *
	 * @return array The filter option values to use in the mm_filteritem_* templates.
	 */
	protected function prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$arrOptions = array();
		if (!isset($arrWidget['options']))
		{
			return $arrOptions;
		}
		$objController = ContaoController::getInstance();

		$strFilterAction = $this->buildFilterUrl($arrFilterUrl, $arrWidget['eval']['urlparam']);

		// If no jumpTo-page has been provided, we use the current page.
		if (!$arrJumpTo)
		{
			$arrJumpTo = $GLOBALS['objPage']->row();
		}

		if ($arrWidget['eval']['includeBlankOption'])
		{
			$blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, '');

			$arrOptions[] = array
			(
				'key'    => '',
				'value'  => (
				$arrWidget['eval']['blankOptionLabel']
					? $arrWidget['eval']['blankOptionLabel']
					: $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']
				),
				'href'   => $objController->generateFrontendUrl($arrJumpTo, sprintf($strFilterAction, '')),
				'active' => $blnActive,
				'class'  => 'doNotFilter'.($blnActive ? ' active' : ''),
			);
		}

		foreach ($arrWidget['options'] as $strKeyOption => $strOption)
		{
			$strValue  = rawurlencode($this->getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption));
			$blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption);

			if (!empty($strValue))
			{
				if ($arrWidget['eval']['urlparam'] !== 'auto_item')
				{
					$strValue = '/' . $arrWidget['eval']['urlparam'] . '/' . $strValue;
				}
				else
				{
					$strValue = '/' . $strValue;
				}
			}

			$arrOptions[] = array
			(
				'key'    => $strKeyOption,
				'value'  => $strOption,
				'href'   => $objController->generateFrontendUrl(
					$arrJumpTo,
					sprintf($strFilterAction, $strValue)
				),
				'active' => $blnActive,
				'class'  => standardize($strKeyOption) . ($blnActive ? ' active' : '')
			);
		}
		return $arrOptions;
	}

	/**
	 * Returns the frontend filter widget information for the filter setting.
	 *
	 * The returning array will hold the following keys:
	 * * class      - The CSS classes for the widget.
	 * * label      - The label text for the widget.
	 * * formfield  - The parsed default widget object for this filter setting.
	 * * raw        - The widget information that was used for rendering "formfield" as raw array (this means
	 *                prior calling prepareForWidget()).
	 * * urlparam   - The URL parameter used for this widget.
	 * * options    - The filter options available to be used in selects etc. see prepareFrontendFilterOptions
	 *                for details on the contained array.
	 * * autosubmit - True if the frontend filter shall perform auto form submitting, false otherwise.
	 * * urlvalue   - The current value selected in the filtersetting. Will use "urlvalue" from $arrWidget with
	 *                fallback to the value of the url param in the filter url.
	 *
	 * @param array                 $arrWidget                The widget information to use for generating.
	 *
	 * @param array                 $arrFilterUrl             The filter url parameters to use.
	 *
	 * @param array                 $arrJumpTo                The jumpTo page to use for URL generating - if empty, the
	 *                                                        current frontend page will get used.
	 *
	 * @param FrontendFilterOptions $objFrontendFilterOptions The options to use.
	 *
	 * @return array
	 */
	protected function prepareFrontendFilterWidget(
		$arrWidget,
		$arrFilterUrl,
		$arrJumpTo,
		FrontendFilterOptions $objFrontendFilterOptions
	)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrWidget['inputType']];

		// No widget? no output! that's it.
		if (!$strClass)
		{
			return array();
		}

		// Determine current value.
		$arrWidget['value'] = isset($arrFilterUrl[$arrWidget['eval']['urlparam']])
			? $arrFilterUrl[$arrWidget['eval']['urlparam']]
			: null;

		$arrData = ContaoController::getInstance()->prepareForWidget(
			$arrWidget,
			$arrWidget['eval']['urlparam'],
			$arrWidget['value']
		);

		if ($objFrontendFilterOptions->isAutoSubmit() && TL_MODE == 'FE')
		{
			$GLOBALS['TL_JAVASCRIPT']['metamodels'] = 'system/modules/metamodels/assets/js/metamodels.js';
		}

		/** @var \Widget $objWidget */
		$objWidget = new $strClass($arrData);

		$strField = $objWidget->generate();

		return array
		(
			'class'      => sprintf(
				'mm_%s %s%s%s',
				$arrWidget['inputType'],
				$arrWidget['eval']['urlparam'],
				(($arrWidget['value'] !== null) ? ' used':' unused'),
				($objFrontendFilterOptions->isAutoSubmit() ? ' submitonchange' : '')
			),
			'label'      => $objWidget->generateLabel(),
			'formfield'  => $strField,
			'raw'        => $arrWidget,
			'urlparam'   => $arrWidget['eval']['urlparam'],
			'options'    => $this->prepareFrontendFilterOptions(
					$arrWidget,
					$arrFilterUrl,
					$arrJumpTo,
					$objFrontendFilterOptions->isAutoSubmit()
				),
			'count'      => isset($arrWidget['count']) ? $arrWidget['count'] : null,
			'showCount'  => $objFrontendFilterOptions->isShowCountValues(),
			'autosubmit' => $objFrontendFilterOptions->isAutoSubmit(),
			'urlvalue'   => array_key_exists('urlvalue', $arrWidget) ? $arrWidget['urlvalue'] : $arrWidget['value']
		);
	}

	/**
	 * This base implementation returns an empty array.
	 *
	 * @param IItem           $objItem          The item to fetch the values from.
	 *
	 * @param IRenderSettings $objRenderSetting The render setting to be applied.
	 *
	 * @return array An empty array.
	 */
	public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
	{
		return array();
	}

	/**
	 * This base implementation returns an empty array.
	 *
	 * @return array Empty array.
	 */
	public function getParameters()
	{
		return array();
	}

	/**
	 * This base implementation returns an empty array.
	 *
	 * @return array Empty array.
	 */
	public function getParameterDCA()
	{
		return array();
	}

	/**
	 * This base implementation returns an empty array.
	 *
	 * @return array Empty array.
	 */
	public function getParameterFilterNames()
	{
		return array();
	}

	/**
	 * Retrieve a list of filter widgets for all registered parameters as form field arrays.
	 *
	 * @param array                 $arrIds                   The ids matching the current filter values.
	 *
	 * @param array                 $arrFilterUrl             The current filter url.
	 *
	 * @param array                 $arrJumpTo                The jumpTo page (array, row data from tl_page).
	 *
	 * @param FrontendFilterOptions $objFrontendFilterOptions The frontend filter options.
	 *
	 * @return array
	 */
	public function getParameterFilterWidgets(
		$arrIds,
		$arrFilterUrl,
		$arrJumpTo,
		FrontendFilterOptions $objFrontendFilterOptions
	)
	{
		return array();
	}

	/**
	 * Retrieve a list of all referenced attributes within the filter setting.
	 *
	 * @return array
	 */
	public function getReferencedAttributes()
	{
		return array();
	}
}

