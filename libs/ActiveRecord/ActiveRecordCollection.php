<?php


/**
 * The ActiveRecordCollection class represents a list of records identified by a query.
 * An ActiveRecordCollection should act like an array in every way, except that it will attempt to defer loading until the records are needed.
 * An ActiveRecordCollection is typically returned by the ActiveRecord::find() or objects() methods.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class ActiveRecordCollection extends ArrayList {

	/** @var Mapper */
	private $mapper;

	/** @var DibiDataSource */
	private $ds;

	/** @var bool */
	private $loaded = FALSE;

	/** @var bool */
	private $reversed = FALSE;


	/**
	 * @param DibiDataSource $ds
	 * @param Mapper         $mapper
	 */
	public function __construct(DibiDataSource $ds, Mapper $mapper) {
		$this->ds = $ds;
		$this->mapper = $mapper;

		parent::__construct(NULL, $mapper->getRowClass());
	}


	/**
	 * Loads the Collection from the repository.
	 * @return void
	 */
	public function load() {
		$res = $this->ds->getResult();
		$res->setRowClass($this->mapper->getRowClass()); // $this->getItemType()
		$res->setTypes($this->mapper->getTypes()); // $res->detectTypes()

		$this->import($res->fetchAll());
		//$this->fetched = TRUE;

		if ($this->reversed)
			$this->reverse();
	}


	/**
	 * Check if is the Collection loaded from the repository.
	 * @return bool
	 */
	public function isLoaded() {
		return (bool) $this->loaded;
	}



	/********************* ActiveRecordCollection data manipulators ********************/



	/**
	 * Adds conditions to query.
	 * @param  mixed      conditions
	 * @return ActiveRecordCollection  provides a fluent interface
	 */
	public function filter($conditions) {
		if (is_array($conditions))
			foreach ($conditions as $condition)
				$this->ds->where($condition);
		else
			$this->ds->where(func_get_args());

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
			$this->ds->orderBy($column);

		} else if (is_string($column) && preg_match('/[, ]+/', $column)) {
			$tmp = $column;
			$column = array();
			foreach(explode(',', $tmp) as $order) {
				$order = explode(' ', trim($order));
				$column[trim($order[0], '[]')] = trim($order[1]);
			}
			$this->ds->orderBy($column);

		} else {
			$this->ds->orderBy($column, $sorting);
		}

		$this->loaded = FALSE;
		return $this;
	}


	/**
	 * Limits number of rows.
	 * @param  int limit
	 * @param  int offset
	 * @return ActiveRecordCollection  provides a fluent interface
	 */
	public function applyLimit($limit, $offset = NULL) {
		$this->ds->applyLimit($limit, $offset);
		$this->loaded = FALSE;
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
		$tmp = $this->getArrayCopy();
		$el = reset($tmp);
		return $el === FALSE ? NULL : $el;
	}


	/**
	 * Return the last Record in the Collection.
	 * @return mixed
	 */
	public function last() {
		$tmp = $this->getArrayCopy();
		$el = end($tmp);
		return $el === FALSE ? NULL : $el;
	}


	/**
	 * Return a copy of the Collection sorted in reverse.
	 * @return ActiveRecordCollection
	 */
	public function reverse() {
		$this->import(array_reverse($this->getArrayCopy()));
		$this->loaded = TRUE;
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
	 * Appends the specified element to the end of this collection.
	 * @param  mixed
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function _append($item) {
		//neni treba, pokud se do updating() prida load()
		//neni treba, osefovano v beforeAdd()
		$this->fetchCheck();
		parent::append($item);
	}



	/**
	 * Removes the first occurrence of the specified element.
	 * @param  mixed
	 * @return bool  true if this collection changed as a result of the call
	 * @throws NotSupportedException
	 */
	public function remove($item) {
		$item->destroy();
		return parent::remove($item);
		//neni treba -> fetchne se v search ktere vola rekurzivne getArrayCopy()
	}



	/**
	 * Returns the index of the first occurrence of the specified element,.
	 * or FALSE if this collection does not contain this element.
	 * @param  mixed
	 * @return int|FALSE
	 */
	protected function search($item) {
		//neni treba delat kontrolu isLoaded() -> fetchne se v getArrayCopy()
		//je treba jen zmenit 3. parametr na FALSE
		return array_search($item, $this->getArrayCopy(), FALSE);
	}



	/**
	 * Removes all Records from the Collection.
	 * @return void
	 */
	public function clear() {
		parent::clear();
		$this->loaded = TRUE;
	}



	/**
	 * Returns true if this collection contains the specified item.
	 * @param  mixed
	 * @return bool
	 */
	public function _contains($item) {
		//neni treba -> fetchne se v search ktere vola rekurzivne getArrayCopy()
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|Traversable
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function import($arr) {
		parent::import($arr);
		$this->loaded = TRUE;
	}



	/********************* internal notifications *********************/



	/**
	 * Responds when the item is about to be added to the collection.
	 * @param  mixed
	 * @return void
	 * @throws InvalidArgumentException, NotSupportedException
	 */
	protected function _beforeAdd($item) {
		//neni treba, pokud se do updating() prida load()
		$this->fetchCheck();
		parent::beforeAdd($item);
	}



	/**
	 * @return void
	 */
	protected function fetchCheck() {
		if (!$this->isLoaded())
			$this->load();
	}



	/********************* counting *********************/



	/**
	 * Get the number of public properties in the ArrayObject
	 * @return int
	 */
	public function count() {
		return $this->isLoaded() ? parent::count() : $this->ds->count();
		//$this->fetchCheck();
		//return parent::count();
	}


	/**
	 * Returns the number of rows in a given data source.
	 * @return int
	 */
	public function getTotalCount() {
		return $this->ds->getTotalCount();
	}



	/********************* ArrayObject cooperation *********************/



	/**
	 * Returns the iterator.
	 * @return ArrayIterator
	 */
	public function _getIterator() {
		//neni treba delat kontrolu isLoaded() -> fetchne se v getArrayCopy()
	}



	/**
	 * Protected exchangeArray().
	 * @param  array  new array
	 * @return Collection  provides a fluent interface
	 */
	protected function setArray($array) {
		parent::setArray($array);
		$this->loaded = TRUE;
		return $this;
	}



	/**
	 * Creates a copy of the ArrayObject.
	 * @return array
	 */
	public function getArrayCopy() {
		$this->fetchCheck();
		return parent::getArrayCopy();
	}



	/**
	 * Creates a copy of the ArrayObject. Alias for getArrayCopy().
	 * @return array
	 */
	public function toArray() {
		return $this->getArrayCopy();
	}



	/********************* interface ArrayAccess ********************/



	/**
	 * Replaces (or appends) the item (ArrayAccess implementation).
	 * @param  int index
	 * @param  object
	 * @return void
	 * @throws InvalidArgumentException, NotSupportedException, ArgumentOutOfRangeException
	 */
	public function offsetSet($index, $item) {
		// TODO: podpora na hromadné settery
		$this->fetchCheck();
		parent::offsetSet($index, $item);
	}



	/**
	 * Returns item (ArrayAccess implementation).
	 * @param  int index
	 * @return mixed
	 * @throws ArgumentOutOfRangeException
	 */
	public function offsetGet($index) {
		$this->fetchCheck();
		return parent::offsetGet($index);
	}



	/**
	 * Exists item? (ArrayAccess implementation).
	 * @param  int index
	 * @return bool
	 */
	public function offsetExists($index) {
		$this->fetchCheck();
		return parent::offsetExists($index);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * @param  int index
	 * @return void
	 * @throws NotSupportedException, ArgumentOutOfRangeException
	 */
	public function offsetUnset($index) {
		$this->fetchCheck();
		parent::offsetUnset($index);
	}
	
}