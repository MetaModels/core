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

namespace MetaModels;

/**
 * Interface for a collection of MetaModel items.
 *
 * @package    MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Items implements IItems
{
	/**
	 * Current reading cursor.
	 *
	 * @var int
	 */
	protected $intCursor = -1;

	/**
	 * Buffer of contained instances.
	 *
	 * @var array
	 */
	protected $arrItems = array();

	/**
	 * Creates a new instance with the passed items.
	 *
	 * @param array $arrItems The items to be contained in the collection.
	 */
	public function __construct($arrItems)
	{
		$this->arrItems = $arrItems;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rewind()
	{
		$this->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function current()
	{
		return $this->getItem();
	}

	/**
	 * {@inheritDoc}
	 */
	public function key()
	{
		return $this->intCursor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function valid()
	{
		return ($this->offsetExists($this->intCursor));
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists($offset)
	{
		if (!is_numeric($offset))
		{
			return false;
		}
		return (($this->getCount() > $offset) && ($offset > -1));
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			return $this->arrItems[$offset];
		}

		return null;
	}

	/**
	 * Not implemented in this class.
	 *
	 * @param mixed $offset The offset to assign the value to.
	 *
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException Always in this base class.
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function offsetSet($offset, $value)
	{
		throw new \RuntimeException('MetaModelItems is a read only class, you can not manipulate the collection.', 1);
	}

	/**
	 * Not implemented in this class.
	 *
	 * @param mixed $offset The offset to assign the value to.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException Always in this base class.
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function offsetUnset ($offset)
	{
		throw new \RuntimeException('MetaModelItems is a read only class, you can not manipulate the collection.', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItem()
	{
		// Implicitly call first when not within "while ($obj->next())" scope.
		if ($this->intCursor < 0)
		{
			$this->first();
		}

		// Beyond bounds? return null.
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
		if ($this->getCount() > 0)
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
		if ($this->getCount() == $this->intCursor)
		{
			return false;
		}
		// We must advance over the last element.
		$this->intCursor += 1;

		// Check the index again, see #461.
		return ($this->getCount() == $this->intCursor) ? false : $this;
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
		$this->intCursor = ($this->getCount() - 1);

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

		if ($this->intCursor == ($this->getCount() - 1))
		{
			$arrClass[] = 'last';
		}

		if (($this->intCursor % 2) == 0)
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
	public function parseValue($strOutputFormat = 'text', $objSettings = null)
	{
		return $this->getItem()->parseValue($strOutputFormat, $objSettings);
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseAll($strOutputFormat = 'text', $objSettings = null)
	{
		$arrResult = array();

		// Buffer cursor.
		$intCursor = $this->intCursor;

		foreach ($this as $objItem)
		{
			$arrParsedItem          = $this->parseValue($strOutputFormat, $objSettings);
			$arrParsedItem['class'] = $this->getClass();
			$arrResult[]            = $arrParsedItem;
		}

		// Restore cursor.
		$this->intCursor = $intCursor;

		return $arrResult;
	}
}

