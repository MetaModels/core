<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Interface for a collection of MetaModel items.
 * 
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelItems implements IMetaModelItems
{

	/**
	 * current reading cursor
	 * 
	 * @var int
	 */
	protected $intCursor = -1;

	/**
	 * buffer of contained instances
	 */
	protected $arrItems = array();

	public function __construct($arrItems)
	{
		$this->arrItems = $arrItems;
	}

	public function rewind()
	{
		$this->first();
	}

	public function current()
	{
		return $this->getItem();
	}

	public function key()
	{
		return $this->intCursor;
	}

	public function valid()
	{
		return ($this->getCount() > $this->intCursor && $this->intCursor>-1);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItem()
	{
		// implicitely call first when not within "while ($obj->next())" scope.
		if ($this->intCursor < 0)
		{
			$this->first();
		}
		// beyond bounds? return null.
		if (!$this->valid())
		{
			return null;
		}
		return $this->arrItems[$this->intCursor];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount()
	{
		return count($this->arrItems);
	}

	/**
	 * {@inheritdoc}
	 */
	public function first()
	{
		if($this->getCount()>0)
		{
			$this->intCursor = 0;
			return $this;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next()
	{
		if ($this->getCount() == ++$this->intCursor)
		{
//			$this->intCursor--;
			return false;
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function prev()
	{
		if ($this->intCursor == 0)
		{
			return false;
		}

		$this->intCursor--;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function last()
	{
		$this->intCursor = $this->getCount() - 1;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset()
	{
		$this->intCursor = -1;
		return $this;
	}

	public function getClass()
	{
		$arrClass = array();
		if ($this->intCursor == 0)
		{
			$arrClass[] = 'first';
		}

		if ($this->intCursor == $this->getCount() -1)
		{
			$arrClass[] = 'last';
		}

		if ($this->intCursor %2 == 0)
		{
			$arrClass[] = 'even';
		} else {
			$arrClass[] = 'odd';
		}
		return implode(' ', $arrClass);
	}

	public function parseValue($strOutputFormat = 'html')
	{
		return $this->getItem()->parseValue($strOutputFormat);
	}
}

?>