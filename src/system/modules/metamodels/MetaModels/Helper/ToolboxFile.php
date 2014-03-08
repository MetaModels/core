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

namespace MetaModels\Helper;

use MetaModels\Helper\ContaoController as MetaModelController;

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
	 * 
	 * @deprecated Remove when we drop support for Contao 2.11 - impossible in Contao 3.
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
	 * Set the base language.
	 *
	 * @param string $baseLanguage
	 *
	 * @return ToolboxFile
	 */
	public function setBaseLanguage($baseLanguage)
	{
		$this->baseLanguage = $baseLanguage;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBaseLanguage()
	{
		return $this->baseLanguage;
	}

	/**
	 * Set the fallback language.
	 *
	 * @param string $fallbackLanguage
	 *
	 * @return ToolboxFile
	 */
	public function setFallbackLanguage($fallbackLanguage)
	{
		$this->fallbackLanguage = $fallbackLanguage;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFallbackLanguage()
	{
		return $this->fallbackLanguage;
	}

	/**
	 * Set to show/prepare images or not.
	 *
	 * @param boolean $blnShowImages
	 *
	 * @return ToolboxFile
	 */
	public function setShowImages($blnShowImages)
	{
		$this->blnShowImages = $blnShowImages;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowImages()
	{
		return $this->blnShowImages;
	}

	/**
	 * Set the resize information.
	 *
	 * @param array $resizeImages
	 *
	 * @return ToolboxFile
	 */
	public function setResizeImages($resizeImages)
	{
		$this->resizeImages = $resizeImages;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getResizeImages()
	{
		return $this->resizeImages;
	}

	/**
	 * Sets the Id to use for the lightbox.
	 *
	 * @param string $strLightboxId
	 *
	 * @return ToolboxFile
	 */
	public function setLightboxId($strLightboxId)
	{
		$this->strLightboxId = $strLightboxId;

		return $this;
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
	 * @return ToolboxFile
	 */
	public function addPath($strPath)
	{
		if (is_file(TL_ROOT . DIRECTORY_SEPARATOR . $strPath))
		{
			$strExtension = pathinfo (TL_ROOT . DIRECTORY_SEPARATOR . $strPath, PATHINFO_EXTENSION);
			if (in_array(strtolower($strExtension), $this->acceptedExtensions))
			{
				$this->foundFiles[] = $strPath;
			}
		}
		elseif(is_dir(TL_ROOT . DIRECTORY_SEPARATOR . $strPath))
		{
			$this->foundFolders[] = $strPath;
		}

		return $this;
	}

	/**
	 * Contao 3 DBAFS Support.
	 * 
	 * @param string $strID Id of the file.
	 * 
	 * @throws \RuntimeException
	 */
	public function addPathById($strID)
	{
		if(version_compare(VERSION, '3.0', '<'))
		{
			throw new \RuntimeException('You cannot use a contao 3 function in a contao 2.x context.');
		}
		
		$objFile = \FilesModel::findByPk($strID);
		
		// ToDo: Should we throw a exception or just return if we have no file.
		if ($objFile !== null)
		{
			$this->addPath($objFile->path);
		}
		
		return $this;		
	}

	/**
	 * Walks the list of pending folders via ToolboxFile::addPath().
	 *
	 * @return void
	 */
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
	 * 
	 * @deprecated Remove when we drop support for Contao 2.11.
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
			// filename.ext = title | url | caption
			list($strLabel, $strValue) = array_map('trim', explode('=', $v, 2));

			$this->metaInformation[$strPath][$strLabel] = array_map('trim', explode('|', $strValue));
			$this->metaInformation[$strPath][$strLabel]['title']   = $this->metaInformation[$strPath][$strLabel][0];
			$this->metaInformation[$strPath][$strLabel]['link']    = $this->metaInformation[$strPath][$strLabel][1];
			$this->metaInformation[$strPath][$strLabel]['caption'] = $this->metaInformation[$strPath][$strLabel][2];

			if (!in_array($strPath . DIRECTORY_SEPARATOR . $strLabel, $this->metaSort))
			{
				$this->metaSort[] = $strPath . DIRECTORY_SEPARATOR . $strLabel;
			}
		}
	}

	/**
	 * Loops all found files and parses the corresponding metafile (pre Contao 3).
	 *
	 * @return void
	 * 
	 * @deprecated Remove when we drop support for Contao 2.11.
	 */
	protected function parseMetaFilesPre3()
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

			$this->parseMetaFile($strDir, $this->getFallbackLanguage());
			$this->parseMetaFile($strDir, $this->getBaseLanguage());
			$this->parseMetaFile($strDir);
		}
	}
	
	/**
	 * Loops all found files and parses the corresponding metafile.
	 *
	 * @return void
	 */
	protected function parseMetaFiles()
	{
		$files = \FilesModel::findMultipleByPaths($this->foundFiles);
		
		if (!$files)
		{
			return;
		}
		
		while ($files->next())
		{
			$path = $files->path;
			$meta = deserialize($files->meta, true);

			if (isset($meta[$this->getBaseLanguage()]))
			{
				$this->metaInformation[dirname($path)][basename($path)] = $meta[$this->getBaseLanguage()];
			}
			elseif (isset($meta[$this->getFallbackLanguage()]))
			{
				$this->metaInformation[dirname($path)][basename($path)] = $meta[$this->getFallbackLanguage()];
			}
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
		$strRequest = \Environment::getInstance()->request;
		if (($intPos = strpos($strRequest, '?')) !== false)
		{
			$strRequest = str_replace('?&', '?', preg_replace('/&?file=[^&]&*/', '', $strRequest));
		}
		$strRequest .= (strpos($strRequest, '?') === false ? '?' : '&');
		$strRequest .= 'file=' . urlencode($strFile);

		return $strRequest;
	}

	/**
	 * Walk all files and fetch desired additional information like image sizes etc.
	 *
	 * @return void
	 */
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
			$objFile = new \File($strFile);

			$arrMeta =$this->metaInformation[dirname($strFile)][$objFile->basename];
			$strBasename = strlen($arrMeta['title']) ? $arrMeta['title'] : specialchars($objFile->basename);
			$strAltText = (strlen($arrMeta['caption']) ? $arrMeta['caption'] : ucfirst(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename))));

			if (version_compare(VERSION, '3.0', '<'))
			{
				$strIcon = 'system/themes/' . $strThemeDir . '/images/' . $objFile->icon;
			} else {
				$strIcon = 'assets/contao/images/' . $objFile->icon;
			}

			$arrSource = array
			(
				'file'     => $strFile,
				'mtime'    => $objFile->mtime,
				'alt'      => $strAltText,
				'caption'  => (strlen($arrMeta['caption']) ? $arrMeta['caption'] : ''),
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

	/**
	 * Maps the sorting from the files to the source.
	 *
	 * All files from $arrFiles are being walked and the corresponding entry from source gets pulled in.
	 *
	 * Additionally, the css classes are applied to the returned 'source' array.
	 *
	 * This returns an array like: array('files' => array(), 'source' => array())
	 *
	 * @param array $arrFiles
	 *
	 * @param array $arrSource
	 *
	 * @return array The mapped result.
	 */
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

	/**
	 * Sorts the internal file list by a given condition.
	 *
	 * Allowed sort types are:
	 * name_asc  - Sort by filename ascending.
	 * name_desc - Sort by filename descending
	 * date_asc  - Sort by modification time ascending.
	 * date_desc - Sort by modification time descending.
	 * meta      - Sort by meta.txt - the order of the files in the meta.txt is being used, however, the files are still
	 *             being grouped by the folders, as the meta.txt is local to a folder and may not span more than one
	 *             level of the file system
	 * random    - Shuffle all the files around.
	 *
	 * @param string $sortType The sort condition to be applied.
	 *
	 * @return array The sorted file list.
	 * 
	 * @deprecated Remove sort by "meta" when we drop support for Contao 2.11.
	 */
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
				. (($k == ($countFiles - 1)) ? ' last' : '')
				. ((($k % 2) == 0) ? ' even' : ' odd');
		}
	}

	/**
	 * Sort by filename.
	 *
	 * @param boolean $blnAscending Flag to determine if sorting shall be applied ascending (default) or descending.
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
	 * Sort by modification time.
	 *
	 * @param boolean $blnAscending Flag to determine if sorting shall be applied ascending (default) or descending.
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
	 * 
	 * @deprecated Remove when we drop support for Contao 2.11.
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
	 * Shuffle the file list.
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

	/**
	 * Returns the file list.
	 *
	 * NOTE: you must call resolveFiles() beforehand as otherwise folders are not being evaluated.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->foundFiles;
	}

	/**
	 * Process all folders and resolve to a valid file list.
	 *
	 * @return ToolboxFile
	 */
	public function resolveFiles()
	{
		// Step 1.: fetch all files.
		$this->collectFiles();

		// TODO: check if downloading is allowed and send file to browser then
		// see https://github.com/MetaModels/attribute_file/issues/6
		if ((!$this->getShowImages()) && ($strFile = \Input::getInstance()->get('file')) && in_array($strFile, $this->foundFiles))
		{
			MetaModelController::getInstance()->sendFileToBrowser($strFile);
		}

		// Step 2.: Fetch all meta data for the found files.
		if (version_compare(VERSION, '3', '<'))
		{
			$this->parseMetaFilesPre3();
		}
		else
		{
			$this->parseMetaFiles();
		}

		// Step 3.: fetch additional information like modification time etc. and prepare the output buffer.
		$this->fetchAdditionalData();

		return $this;
	}

	/**
	 * Translate the file ID to file path
	 */
	public function convertValueToPath($varValue)
	{

		$objFiles = \FilesModel::findByPk($varValue);

		if ($objFiles !== null)
		{
			return $objFiles->path;
		}
		return '';
	}
}
