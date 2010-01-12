<?php


/**
 * An ActiveRecordCollection class represents a list of records identified by a query.
 * An ActiveRecordCollection should act like an array in every way, except that it will attempt to defer loading until the records are needed.
 * An ActiveRecordCollection is typically returned by the ActiveRecord::findAll() or objects() methods.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class ActiveRecordCollection extends LazyArrayList {

	/** @var DibiDataSource */
	private $source;

	/** @var bool */
	private $reversed = FALSE;

	/** @var bool */
	public static $loadImmediately = FALSE;


	/**
	 * @param DibiDataSource $source
	 * @param string         $class
	 */
	public function __construct(DibiDataSource $source, $class) {
		if (!class_exists($class))
			throw new InvalidArgumentException("Class '$class' not found.");

		$this->source = $source;
		parent::__construct(NULL, $class);

		if (self::$loadImmediately)
			$this->load();
	}


	/**
	 * Loads the Collection from the repository.
	 * @return ActiveRecordCollection  provides a fluent interface
	 */
	public function load() {
		$res = $this->source->getResult();
		$res->setRowClass($this->getItemType());
		$res->detectTypes();
		$this->import($res->fetchAll());

		if ($this->reversed)
			$this->reverse();

		return $this;
	}


	/**
	 * Public property getter.
	 * @return DibiDataSource
	 */
	public function getSource() {
		return $this->source;
	}


	/**
	 * Public property setter.
	 * @param  DibiDataSource $source
	 */
	public function setSource(DibiDataSource $source) {
		$this->source = $source;
	}



	/********************* ActiveRecordCollection data manipulators ********************/



	/**
	 * Adds conditions to query.
	 * @param  mixed      conditions
	 * @return ActiveRecordCollection  provides a fluent interface
	 */
	public function filter($cond) {
		if (is_array($cond))
			foreach ($cond as $c)
				$this->source->where($c);
		else
			$this->source->where(func_get_args());

		$this->loaded = FALSE;
		return $this;
	}


	/**
	 * Selects columns to order by.
	 * @param  string|array  column name or array of column names
	 * @param  string  		 sorting direction
	 * @return ActiveRecordCollection     provides a fluent interface
	 */
	public function orderBy($column, $sorting = 'ASC') {
		if (is_array($column)) {
			$this->source->orderBy($column);

		} else if (is_string($column) && preg_match('/[, ]+/', $column)) {
			$tmp = $column;
			$column = array();
			foreach(explode(',', $tmp) as $order) {
				$order = explode(' ', trim($order));
				$column[trim($order[0], '[]')] = trim($order[1]);
			}
			$this->source->orderBy($column);

		} else {
			$this->source->orderBy($column, $sorting);
		}

		$this->invalidate();
		return $this;
	}


	/**
	 * Limits number of rows.
	 * @param  int limit
	 * @param  int offset
	 * @return ActiveRecordCollection  provides a fluent interface
	 */
	public function applyLimit($limit, $offset = NULL) {
		$this->source->applyLimit($limit, $offset);
		$this->invalidate();
		return $this;
	}



	/********************* ActiveRecordCollection mapper shortcuts ********************/


	/**
	 * Checks if any Records have unsaved changes.
	 * @return bool
	 */
	public function isDirty() {
		throw new NotImplementedException;
	}


	/**
	 * Checks if all the Records have no changes to save.
	 * @return bool
	 */
	public function isClean() {
		throw new NotImplementedException;
	}


	/**
	 * Save every Record in the Collection.
	 * @return ActiveRecordCollection
	 */
	public function save() {
		throw new NotImplementedException;
	}


	/**
	 * Remove every Record in the Collection from the repository.
	 * @return void
	 */
	public function destroy() {
		throw new NotImplementedException;
	}


	/**
	 * Reset every Record unsaved changes to a similar state as a new Record (thus making all properties non dirty).
	 * @return ActiveRecordCollection
	 */
	public function discard() {
		throw new NotImplementedException;
	}


	/**
	 * Create a Record in the Collection. / Initializes a Record and appends it to the Collection.
	 * @return ActiveRecord
	 */
	public function create() {
		throw new NotImplementedException;
	}



	/********************* ActiveRecordCollection helpers ********************/



	/**
	 * Return the first Record in the Collection.
	 * @return mixed
	 */
	public function first() {
		if (!$this->isLoaded() && !$this->reversed) {
			$clone = clone $this;
			$clone->source = $clone->source->toDataSource()->applyLimit(1);
			$clone->load();
			return $clone->first();
		}

		$copy = $this->getArrayCopy();
		$el = reset($copy);
		return $el === FALSE ? NULL : $el;
	}


	/**
	 * Return the last Record in the Collection.
	 * @return mixed
	 */
	public function last() {
		if (!$this->isLoaded() && !$this->reversed) {
			$clone = clone $this;
			$clone->source = $clone->source->toDataSource()->applyLimit(1, $this->count()-1);
			$clone->load();
			return $clone->last();
		}

		$copy = $this->getArrayCopy();
		$el = end($copy);
		return $el === FALSE ? NULL : $el;
	}


	/**
	 * Return a copy of the Collection sorted in reverse.
	 * @return void  intentionally not fluent
	 */
	public function reverse() {
		$this->import(array_reverse($this->getArrayCopy()));
		$this->reversed = TRUE;
		return $this;
	}


	/**
	 * Removes and returns the first Record in the Collection.
	 * @return ActiveRecord
	 */
	public function shift() {
		$item = $this->first();
		$this->remove($item);
		return $item;
	}


	/**
	 * Removes and returns the last Record in the Collection.
	 * @return ActiveRecord
	 */
	public function pop() {
		$item = $this->last();
		$this->remove($item);
		return $item;
	}


	/**
	 * Append Record to the Collection.
	 * @return ActiveRecord
	 */
	public function push(ActiveRecord $item) {
		$this->append($item);
	}



	/********************* Collection cooperation *********************/



	/**
	 * Removes the first occurrence of the specified element.
	 * @param  mixed
	 * @return bool  true if this collection changed as a result of the call
	 * @throws NotSupportedException
	 */
	public function remove($item) {
		$this->typeCheck($item);
		$removed = parent::remove($item);

		if ($removed)
			$item->destroy();

		return $removed;
	}



	/**
	 * Returns the index of the first occurrence of the specified element,.
	 * or FALSE if this collection does not contain this element.
	 * @param  mixed
	 * @return int|FALSE
	 */
	protected function search($item) {
		return array_search($item, $this->getArrayCopy(), FALSE);
	}


	/**
	 * @param  mixed
	 * @return void
	 * @throws InvalidArgumentException
	 */
	protected function typeCheck($item) {
		if (!($item instanceof $this->itemType))
			throw new InvalidArgumentException("Item must be '$this->itemType' object.");
	}



	/********************* Countable interface *********************/



	/**
	 * Get the number of public properties in the ArrayObject
	 * @return int
	 */
	public function count() {
		return $this->isLoaded() ? parent::count() : $this->source->count();
	}


	/**
	 * Returns the number of rows in a given data source.
	 * @return int
	 */
	public function getTotalCount() {
		return $this->source->getTotalCount();
	}



	/********************* ArrayAccess interface *********************/



	/**
	 * Removes the element at the specified position in this list.
	 * @param  int index
	 * @return void
	 * @throws NotSupportedException, ArgumentOutOfRangeException
	 */
	public function offsetUnset($index) {
		$this->loadCheck();
		if ($this->offsetExists($index))
			$this->offsetGet($index)->destroy();
		
		parent::offsetUnset($index);
	}



	/********************* magic methods *********************/



	/**
	 * Returns property value. Do not call directly.
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		// TODO: mass getter
		return parent::__get($name);
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value) {
		// TODO: mass setter
		return parent::__set($name, $value);
	}
	
}