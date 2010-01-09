<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 * 
 * @property-read array $values
 * @property-read array $modifiedValues
 * @property-read array $originalValues
 */
class Record extends FreezableObject implements ArrayAccess {

	/** @var DataStorage internal record data storage */
	private $storage;

	/** @var array  internal column name storage */
	protected $columns = array();

	/** @var array  internal default values storage */
	protected $defaults = array();

	/** @var array (column => type)  internal values data types storage */
	protected $types = array();

	/** @var bool  record state sign */
	private $state;

	/**#@+ Record state */
	const STATE_EXISTING = '%E';
	const STATE_NEW = '%N';
	const STATE_DELETED = '%D';
	/**#@-*/

	
	/**
	 * Record constructor.
	 * @param ArrayObject|array $input
	 * @param int $state  does data physically exists in database?
	 */
	public function __construct($input = array(), $state = NULL) {
		if (!is_array($input) && !$input instanceof ArrayObject)
			throw new InvalidArgumentException("Provided input is not array or ArrayObject, '" . gettype($input) . "' given.");

		if ($state === NULL)
			$state = $this->detectState((array) $input);
		if ($state != self::STATE_NEW && $state != self::STATE_EXISTING)
			throw new InvalidArgumentException("Unknow record state '$state' given");
		
		$this->state = $state;
		$this->storage = new DataStorage;
		
		$values = (array) $input + $this->getDefaultValues();
		foreach ($this->getColumnNames() as $column)
			$this->storage->original[$column] = isset($values[$column]) ? $values[$column] : NULL;

		if ($this->isRecordNew())
			$this->storage->modified = $this->storage->original;
	}


	/**
	 * Gets record's internal data stroge object
	 * @retrun DataStorage
	 */
	protected function getStorage() {
		return $this->storage;
	}

	
	/**
	 * Detects record's state.
	 * @param array $input
	 * @return int
	 */
	protected function detectState(array $input) {
		return count($this->getColumnNames()) !== count($input) || count($input) == 0 ? self::STATE_NEW : self::STATE_EXISTING;
	}

	
	/**
	 * Gets record's columns names
	 * @retrun array
	 */
	protected function getColumnNames() {
		return $this->columns;
	}



	/********************* values exchange *********************/



	/**
	 * Gets record's values in array(column => value)
	 * @retrun array
	 */
	public function getValues() {
		return RecordHelper::getValues($this, $this->getColumnNames());
	}


	/**
	 * Sets record's values in array(column => value)
	 * @param array $input
	 */
	public function setValues(array $input) {
		RecordHelper::setValues($this, $input);
	}


	/**
	 * Gets record's modified values in array(column => value)
	 * @return array
	 */
	protected function getModifiedValues() {
		return RecordHelper::getValues($this, array_keys($this->storage->modified));
	}


	/**
	 * Gets record's original values in array(column => value)
	 * @return array
	 */
	protected function getOriginalValues() {
		return $this->getStorage()->original;
	}


	/**
	 * Gets record's default values data of NOT NULL columns in array(column => defaultValue)
	 * @retrun array
	 */
	protected function getDefaultValues() {
		return $this->defaults;
	}



	/********************* record state *********************/



	/**
	 * Is record existing?
	 * @return bool
	 */
	public function isRecordExisting() {
		return $this->state === self::STATE_EXISTING;
	}


	/**
	 * Is record new?
	 * @return bool
	 */
	public function isRecordNew() {
		return $this->state === self::STATE_NEW;
	}


	/**
	 * Is record deleted?
	 * @return bool
	 */
	public function isRecordDeleted() {
		return $this->state === self::STATE_DELETED;
	}



	/********************* record executors *********************/



	/**
	 * Checks if the Record has unsaved changes.
	 * @return bool
	 */
	public function isDirty() {
		return (bool) count($this->storage->modified);
	}


	/**
	 * Checks if the Record has no changes to save.
	 * @return bool
	 */
	public function isClean() {
		return !$this->isDirty();
	}


	/**
	 * Makes all properties Record's non dirty.
	 * @return void
	 */
	protected function clean() {
		$this->storage->modified = array();
	}


	/**
	 * Saves the Record.
	 * @return Record
	 */
	public function save() {
		$this->updating();

		if ($this->isDirty()) {
			$this->storage->original = $this->getValues();
			$this->storage->modified = array(); // $this->clean();
			$this->state = self::STATE_EXISTING;
		}
		return $this;
	}


	/**
	 * Destroy the instance.
	 * @return bool  true if Record was destroyed
	 */
	public function destroy() {
		$this->updating();

		$this->storage->modified = array(); // $this->clean();
		foreach ($this->storage->original as & $v)
			$v = NULL;
		$this->state = self::STATE_DELETED;
		$this->freeze();
	}


	/**
	 * Reset Record's unsaved changes to a similar state as a new Record (thus making all properties non dirty).
	 * @return Record
	 */
	public function discard() {
		$this->updating();
		
		$this->storage->modified = $this->isRecordNew() ? $this->storage->original : array();
		/** alternate:
		if ($this->isRecordNew())
			$this->storage->modified = $this->storage->original;
		else
			$this->clean();
		*/
		return $this;
	}



	/********************* magic getters & setters *********************/



	/**
	 *
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return mixed
	 */
	protected function cast($name, $value) {
		if ($value === NULL)
			return $value;

		switch ($this->types[$name]) {
			case dibi::TEXT: $value = (string) $value; break;
			case dibi::BOOL: $value = (bool) $value; break;
			case dibi::INTEGER: $value = (int) $value; break;
			case dibi::FLOAT: $value = (float) $value; break;
			case dibi::DATE:
			case dibi::TIME:
			case dibi::DATETIME: $value = ($value instanceof DateTime) ? $value : new DateTime($value); break;
			case dibi::BINARY:
			default: break;
		}
		return $value;
	}



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {

		try {
			return parent::__get($name);
			
		} catch(MemberAccessException $e) {
			if (isset($this->storage->$name)) {
				$value = $this->storage->$name;
				$value = $this->cast($name, $value);
				return $value; // PHP work-around (Only variable references should be returned by reference)
			} else {
				throw $e;
			}
		}
	}


	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value) {
		$this->updating();
		
		try {
			parent::__set($name, $value);
			
		} catch(MemberAccessException $e) {
			if (isset($this->storage->$name)) {
				$value = $this->cast($name, $value);
				$this->storage->$name = $value;
			} else {
				throw $e;
			}
		}
	}


	/**
	 * Is property defined?
	 *
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name) {
		return parent::__isset($name) ? TRUE : (array_key_exists($name, $this->storage->original) || array_key_exists($name, $this->storage->modified));
	}


	/**
	 * Unset of property.
	 *
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name) {
		throw new NotSupportedException("Cannot unset the property $this->class::\$$name.");
	}



	/********************* interface ArrayAccess *********************/



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string $offset  property name
	 * @return mixed           property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	final public function offsetGet($offset) {
		return $this->__get($offset);
	}

	
	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param  string $offset  property name
	 * @param  mixed  $value   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	final public function offsetSet($offset, $value) {
		return $this->__set($offset, $value);
	}

	
	/**
	 * Is property defined?
	 *
	 * @param  string $offset  property name
	 * @return bool
	 */
	final public function offsetExists($offset) {
		return $this->__isset($offset);
	}


	/**
	 * Unset of property.
	 *
	 * @param  string $offset  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	final public function offsetUnset($offset) {
		$this->__unset($offset);
	}
}