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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

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

	/**
	 * creates a new instance with the passed items.
	 *
	 * @param array $arrItems the items to be contained in the collection.
	 *
	 * @return IMetaModelItems the new instance.
	 */
	public function __construct($arrItems)
	{
		$this->arrItems = $arrItems;
	}

	/////////////////////////////////////////////////////////////////
	// interface Iterator
	/////////////////////////////////////////////////////////////////

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
		return ($this->offsetExists($this->intCursor));
	}

	/////////////////////////////////////////////////////////////////
	// interface ArrayAccess
	/////////////////////////////////////////////////////////////////

	public function offsetExists($offset)
	{
		if (!is_numeric($offset))
		{
			return false;
		}
		return ($this->getCount() > $offset && $offset>-1);
	}

	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			return $this->arrItems[$offset];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Not implemented in this class.
	 *
	 * @throws Exception
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function offsetSet($offset, $value)
	{
		throw new Exception('MetaModelItems is a read only class, you can not manipulate the collection.', 1);
	}

	/**
	 * Not implemented in this class.
	 *
	 * @throws Exception
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function offsetUnset ($offset)
	{
		throw new Exception('MetaModelItems is a read only class, you can not manipulate the collection.', 1);
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelItems
	/////////////////////////////////////////////////////////////////

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

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	public function parseValue($strOutputFormat = 'text', $objSettings = NULL)
	{
		return $this->getItem()->parseValue($strOutputFormat, $objSettings);
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseAll($strOutputFormat = 'text', $objSettings = NULL)
	{
		$arrResult = array();

		// buffer cursor
		$intCursor = $this->intCursor;

		foreach ($this as $objItem)
		{
			$arrParsedItem = $this->parseValue($strOutputFormat, $objSettings);
			$arrParsedItem['class'] = $this->getClass();
			$arrResult[] = $arrParsedItem;
		}
		// restore cursor
		$this->intCursor = $intCursor;

		return $arrResult;
	}
}

