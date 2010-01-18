<?php


/**
 * An ActiveCollection class represents a list of records identified by a query.
 * An ActiveCollection should act like an array in every way, except that it will attempt to defer loading until the records are needed.
 * An ActiveCollection is typically returned by the ActiveRecord::findAll() or objects() methods.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class ActiveCollection extends LazyArrayList {

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
	 * @return ActiveCollection  provides a fluent interface
	 */
	public function load() {
		$ds = clone $this->getSource(); // intentionally clone (to not seek)
		$res = $ds->getResult();
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


	/**
	 * Returns all records array like $key => $value pairs.
	 * @param  string  associative key
	 * @param  string  value
	 * @return array
	 */
	public function getPairs($key = NULL, $value = NULL) {
		$class = $this->getItemType();
		if ($key === NULL)
			$key = $class::getPrimaryKey();

		$pairs = array();
		$this->loadCheck();
		foreach ($this->getIterator() as $item)
			$pairs[$item->$key] = $item->$value;
		return $pairs;
	}



	/********************* ActiveCollection data manipulators ********************/



	/**
	 * Adds conditions to query.
	 * @param  mixed      conditions
	 * @return ActiveCollection  provides a fluent interface
	 */
	public function filter($cond) {
		if (is_array($cond))
			foreach ($cond as $c)
				$this->source->where($c);
		else
			$this->source->where(func_get_args());

		$this->invalidate();
		return $this;
	}


	/**
	 * Selects columns to order by.
	 * @param  string|array  column name or array of column names
	 * @param  string  		 sorting direction
	 * @return ActiveCollection     provides a fluent interface
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
	 * @return ActiveCollection  provides a fluent interface
	 */
	public function applyLimit($limit, $offset = NULL) {
		$this->source->applyLimit($limit, $offset);
		$this->invalidate();
		return $this;
	}



	/********************* ActiveCollection mapper shortcuts ********************/



	/**
	 * Checks if any Records have unsaved changes.
	 * @return bool
	 */
	public function isDirty() {
		foreach ($this->getIterator() as $item)
			if ($item->isDirty())
				return TRUE;
		return FALSE;
	}


	/**
	 * Save every Record in the Collection.
	 * @return ActiveCollection
	 */
	public function save() {
		foreach ($this->getIterator() as $item)
			$item->save();
		$this->invalidate();
	}


	/**
	 * Remove every Record in the Collection from the repository.
	 * @return void
	 */
	public function destroy() {
		foreach ($this->getIterator() as $item)
			$item->destroy();
		$this->invalidate();
	}


	/**
	 * Reset every Record unsaved changes to a similar state as a new Record (thus making all properties non dirty).
	 * @return ActiveCollection
	 */
	public function discard() {
		foreach ($this->getIterator() as $item)
			$item->discard();
		$this->invalidate();
	}


	/**
	 * Create a Record in the Collection. / Initializes a Record and appends it to the Collection.
	 * @return ActiveRecord
	 */
	public function create($input = array()) {
		$class = $this->getItemType();
		$item = $class::create($input);
		$this->append($item);
	}



	/********************* ActiveCollection helpers ********************/



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
	public function search($item) {
		$class = $this->getItemType();
		$primary = $class::getPrimaryKey();
		foreach ($this->getIterator() as $key => $element)
			if ($item instanceof ActiveRecord && $element->originals->$primary === $item->originals->$primary)
				return $key;
		return FALSE;
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
		$this->loadCheck();
		$class = $this->getItemType();
		if ($class::hasAttribute($name)) {
			$arr = array();
			foreach ($this->getIterator() as $item)
				$arr[] = $item->$name;
			return $arr;
		} else
			return parent::__get($name);
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value) {
		$this->loadCheck();
		$class = $this->getItemType();
		if ($class::hasAttribute($name)) {
			foreach ($this->getIterator() as $item)
				$item->$name = $value;
		} else
			return parent::__set($name, $value);
	}
	
}