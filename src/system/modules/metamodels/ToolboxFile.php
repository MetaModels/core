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

class ToolboxFile
{
	/**
	 * Allowed file extensions.
	 *
	 * @var array
	 */
	protected $acceptedExtensions;

	/**
	 * Base language, used for retrieving meta.txt information.
	 *
	 * @var string
	 */
	protected $baseLanguage;

	/**
	 * The fallback language, used for retrieving meta.txt information.
	 *
	 * @var string
	 */
	protected $fallbackLanguage;

	/**
	 * Determines if we want to generate images or not.
	 *
	 * @var boolean
	 */
	protected $blnShowImages;

	/**
	 * Image resize information.
	 *
	 * @var array
	 */
	protected $resizeImages;

	/**
	 * The id to use in lightboxes.
	 *
	 * @var string
	 */
	protected $strLightboxId;

	/**
	 * The files to process in this instance.
	 *
	 * @var array
	 */
	protected $foundFiles;

	/**
	 * The folders to process in this instance.
	 *
	 * @var array
	 */
	protected $foundFolders;

	/**
	 * Meta information for files.
	 *
	 * @var array
	 */
	protected $metaInformation;


	/**
	 * Meta sorting information for files.
	 *
	 * @var array
	 */
	protected $metaSort;

	/**
	 * Buffered file information.
	 *
	 * @var array
	 */
	protected $outputBuffer;

	/**
	 * Buffered modification timestamps.
	 *
	 * @var array
	 */
	protected $modifiedTime;

	public function __construct()
	{
		// Initialize some values to sane base.
		$this->setAcceptedExtensions(trimsplit(',', $GLOBALS['TL_CONFIG']['allowedDownload']));
	}

	/**
	 * Set the allowed file extensions.
	 *
	 * @param string|array $acceptedExtensions
	 */
	public function setAcceptedExtensions($acceptedExtensions)
	{
		// We must not allow file extensions that are globally disabled.
		$allowedDownload = trimsplit(',', $GLOBALS['TL_CONFIG']['allowedDownload']);

		if(!is_array($acceptedExtensions))
		{
			$acceptedExtensions = trimsplit(',', $acceptedExtensions);
		}

		$this->acceptedExtensions = array_map('strtolower', array_intersect($allowedDownload, $acceptedExtensions));
	}

	/**
	 * Retrieve the allowed file extensions.
	 *
	 * @return array
	 */
	public function getAcceptedExtensions()
	{
		return $this->acceptedExtensions;
	}

	/**
	 * @param string $baseLanguage
	 */
	public function setBaseLanguage($baseLanguage)
	{
		$this->baseLanguage = $baseLanguage;
	}

	/**
	 * @return string
	 */
	public function getBaseLanguage()
	{
		return $this->baseLanguage;
	}

	/**
	 * @param string $fallbackLanguage
	 */
	public function setFallbackLanguage($fallbackLanguage)
	{
		$this->fallbackLanguage = $fallbackLanguage;
	}

	/**
	 * @return string
	 */
	public function getFallbackLanguage()
	{
		return $this->fallbackLanguage;
	}

	/**
	 * @param boolean $blnShowImages
	 */
	public function setShowImages($blnShowImages)
	{
		$this->blnShowImages = $blnShowImages;
	}

	/**
	 * @return boolean
	 */
	public function getShowImages()
	{
		return $this->blnShowImages;
	}

	/**
	 * @param array $resizeImages
	 */
	public function setResizeImages($resizeImages)
	{
		$this->resizeImages = $resizeImages;
	}

	/**
	 * @return array
	 */
	public function getResizeImages()
	{
		return $this->resizeImages;
	}

	/**
	 * @param string $strLightboxId
	 */
	public function setLightboxId($strLightboxId)
	{
		$this->strLightboxId = $strLightboxId;
	}

	/**
	 * @return string
	 */
	public function getLightboxId()
	{
		return $this->strLightboxId;
	}

	/**
	 * Add path to file or folder list.
	 *
	 * @param $strPath
	 *
	 * @return void
	 */
	public function addPath($strPath)
	{
		if (is_file(TL_ROOT . DIRECTORY_SEPARATOR . $strPath))
		{
			if (in_array(substr($strPath, -3), $this->acceptedExtensions))
			{
				$this->foundFiles[] = $strPath;
			}
		}
		elseif(is_dir(TL_ROOT . DIRECTORY_SEPARATOR . $strPath))
		{
			$this->foundFolders[] = $strPath;
		}
	}

	protected function collectFiles()
	{
		if (count($this->foundFolders))
		{
			while ($strPath = array_pop($this->foundFolders))
			{
				foreach (scan(TL_ROOT . DIRECTORY_SEPARATOR . $strPath) as $strSubfile)
				{
					$this->addPath($strPath . DIRECTORY_SEPARATOR . $strSubfile);
				}
			}
		}
	}

	/**
	 * Parse the meta.txt file of a folder. This is an altered version and differs from the
	 * Contao core function as it also checks the fallback language.
	 *
	 * @param string $strPath     The path where to look for the meta.txt.
	 *
	 * @param string $strLanguage The language of the meta.txt to be searched.
	 *
	 * @return void
	 */
	protected function parseMetaFile($strPath, $strLanguage='')
	{
		$strFile = $strPath . DIRECTORY_SEPARATOR . 'meta' . (strlen($strLanguage) ? '_' . $strLanguage : '') . '.txt';

		if (!file_exists(TL_ROOT . DIRECTORY_SEPARATOR . $strFile))
		{
			return;
		}

		$strBuffer = file_get_contents(TL_ROOT . DIRECTORY_SEPARATOR . $strFile);
		$strBuffer = utf8_convert_encoding($strBuffer, $GLOBALS['TL_CONFIG']['characterSet']);
		$arrBuffer = array_filter(trimsplit('[\n\r]+', $strBuffer));

		foreach ($arrBuffer as $v)
		{
			list($strLabel, $strValue) = array_map('trim', explode('=', $v, 2));

			$this->metaInformation[$strPath][$strLabel] = array_map('trim', explode('|', $strValue));

			if (!in_array($strPath . DIRECTORY_SEPARATOR . $strLabel, $this->metaSort))
			{
				$this->metaSort[] = $strPath . DIRECTORY_SEPARATOR . $strLabel;
			}
		}
	}

	protected function parseMetafiles()
	{
		$this->metaInformation = array();
		$this->metaSort        = array();

		if (!$this->foundFiles)
		{
			return;
		}

		$arrProcessed = array();

		foreach($this->foundFiles as $strFile)
		{

			$strDir = dirname($strFile);
			if (in_array($strDir, $arrProcessed))
			{
				continue;
			}

			$arrProcessed[] = $strDir;

			$this->parseMetaFile($strDir, $this->getBaseLanguage());
			$this->parseMetaFile($strDir, $this->getFallbackLanguage());
			$this->parseMetaFile($strDir);
		}
	}

	/**
	 * Generate an URL for downloading the given file.
	 *
	 * @param string $strFile The file that shall be downloaded.
	 *
	 * @return string
	 */
	protected function getDownloadLink($strFile)
	{
		$strRequest = Environment::getInstance()->request;
		if (($intPos = strpos($strRequest, '?')) !== false)
		{
			$strRequest = str_replace('?&', '?', preg_replace('/&?file=[^&]&*/', '', $strRequest));
		}
		$strRequest .= (strpos($strRequest, '?') === false ? '?' : '&');
		$strRequest .= 'file=' . urlencode($strFile);

		return $strRequest;
	}

	protected function fetchAdditionalData()
	{
		$this->modifiedTime = array();
		$this->outputBuffer = array();

		if (!$this->foundFiles)
		{
			return;
		}

		$objController = MetaModelController::getInstance();
		$strThemeDir = $objController->getTheme();
		$resizeInfo = $this->getResizeImages();
		$intWidth = $resizeInfo[0] ? $resizeInfo[0] : '';
		$intHeight = $resizeInfo[1] ? $resizeInfo[1] : '';
		$strMode = $resizeInfo[2] ? $resizeInfo[2] : '';

		foreach ($this->foundFiles as $strFile)
		{
			$objFile = new File($strFile);

			$arrMeta =$this->metaInformation[dirname($strFile)][$objFile->basename];
			$strBasename = strlen($arrMeta[0]) ? $arrMeta[0] : specialchars($objFile->basename);
			$strAltText = (strlen($arrMeta[0]) ? $arrMeta[0] : ucfirst(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename))));

			$strIcon = 'system/themes/' . $strThemeDir . '/images/' . $objFile->icon;
			$arrSource = array
			(
				'file'     => $strFile,
				'mtime'    => $objFile->mtime,
				'alt'      => $strAltText,
				'caption'  => (strlen($arrMeta[2]) ? $arrMeta[2] : ''),
				'title'    => $strBasename,
				'metafile' => $arrMeta,
				'icon'     => $strIcon,
				'size'     => $objFile->filesize,
				'sizetext' => sprintf('(%s)', $objController->getReadableSize($objFile->filesize, 2)),
				'url'      => specialchars($this->getDownloadLink($strFile))
			);

			// images
			if ($arrSource['isGdImage'] = $objFile->isGdImage)
			{
				if ($this->getShowImages() && ($intWidth || $intHeight || $strMode))
				{
					$strSrc = $objController->getImage($objController->urlEncode($strFile), $intWidth, $intHeight, $strMode);
				} else {
					$strSrc = $strFile;
				}
				$arrSource['src'] = $strSrc;

				$size = getimagesize(TL_ROOT . '/' . urldecode($strSrc));
				$arrSource['lb'] = 'lb'.$this->getLightboxId();
				$arrSource['w'] = $size[0];
				$arrSource['h'] = $size[1];
				$arrSource['wh'] = $size[3];
			}

			$this->modifiedTime[] = $objFile->mtime;
			$this->outputBuffer[] = $arrSource;
		}

	}

	protected function remapSorting($arrFiles, $arrSource)
	{
		$files  = array();
		$source = array();

		// re-sort the values
		foreach($arrFiles as $k => $v)
		{
			$files[]  = $arrFiles[$k];
			$source[] = $arrSource[$k];
		}

		$this->addClasses($source);

		return array
		(
			'files' => $files,
			'source' => $source
		);
	}

	public function sortFiles($sortType)
	{
		switch ($sortType)
		{
			default:
			case 'name_asc':
				return $this->sortByName(true);
				break;

			case 'name_desc':
				return $this->sortByName(false);
				break;

			case 'date_asc':
				return $this->sortByDate(true);
				break;

			case 'date_desc':
				return $this->sortByDate(false);
				break;

			case 'meta':
				return $this->sortByMeta();
				break;

			case 'random':
				return $this->sortByRandom();
				break;
		}
	}

	/**
	 * Attach first, last and even/odd classes to the given array.
	 *
	 * @param $arrSource
	 *
	 * @return void
	 */
	protected function addClasses(&$arrSource)
	{
		$countFiles = count($arrSource);
		foreach($arrSource as $k=>$v)
		{
			$arrSource[$k]['class'] = (($k == 0) ? ' first' : '')
				. (($k == ($countFiles -1 )) ? ' last' : '')
				. ((($k % 2) == 0) ? ' even' : ' odd');
		}
	}

	/**
	 * Sort by
	 *
	 * @return array
	 */
	protected function sortByName($blnAscending = true)
	{
		$arrFiles = $this->foundFiles;

		if (!$arrFiles)
		{
			return array('files' => array(), 'source' => array());
		}

		if ($blnAscending)
		{
			uksort($arrFiles, 'basename_natcasecmp');
		}
		else
		{
			uksort($arrFiles, 'basename_natcasercmp');
		}

		return $this->remapSorting($arrFiles, $this->outputBuffer);
	}

	/**
	 * Sort by
	 *
	 * @return array
	 */
	protected function sortByDate($blnAscending = true)
	{
		$arrFiles = $this->foundFiles;
		$arrDates = $this->modifiedTime;

		if (!$arrFiles)
		{
			return array('files' => array(), 'source' => array());
		}

		if ($blnAscending)
		{
			array_multisort($arrFiles, SORT_NUMERIC, $arrDates, SORT_ASC);
		}
		else
		{
			array_multisort($arrFiles, SORT_NUMERIC, $arrDates, SORT_DESC);
		}

		return $this->remapSorting($arrFiles, $this->outputBuffer);
	}

	/**
	 * Sort by meta.txt
	 *
	 * @return array
	 */
	protected function sortByMeta()
	{
		$arrFiles  = $this->foundFiles;
		$arrSource = $this->outputBuffer;
		$arrMeta   = $this->metaSort;

		if (!$arrMeta)
		{
			return array('files' => array(), 'source' => array());
		}

		$files = array();
		$source = array();

		foreach ($arrMeta as $aux)
		{
			$k = array_search($aux, $arrFiles);

			if ($k !== false)
			{
				$files[] = $arrFiles[$k];
				$source[] = $arrSource[$k];
			}
		}

		$this->addClasses($source);

		return array
		(
			'files' => $files,
			'source' => $source
		);
	}

	/**
	 * Sort by random.
	 *
	 * @return array
	 */
	protected function sortByRandom()
	{
		$arrFiles  = $this->foundFiles;
		$arrSource = $this->outputBuffer;

		if (!$arrFiles)
		{
			return array('files' => array(), 'source' => array());
		}

		$keys = array_keys($arrFiles);
		shuffle($keys);
		foreach($keys as $key)
		{
			$files[$key] = $arrFiles[$key];
		}

		return $this->remapSorting($arrFiles, $arrSource);
	}

	public function getFiles()
	{
		return $this->foundFiles;
	}

	/**
	 * Process all folders and resolve to a valid file list.
	 *
	 * @return void
	 */
	public function resolveFiles()
	{
		// Step 1.: fetch all files.
		$this->collectFiles();

		// Step 2.: Fetch all meta data for the found files.
		$this->parseMetafiles();

		// Step 3.: fetch additional information like modification time etc. and prepare the output buffer.
		$this->fetchAdditionalData();
	}
}
