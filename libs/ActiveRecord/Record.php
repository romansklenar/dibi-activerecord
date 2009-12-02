<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 * 
 * @property-read array $values
 * @property-read array $modifiedValues
 * @property-read array $backupValues
 */
class Record extends FreezableObject implements ArrayAccess {

	/** @var DataStorage */
	protected $storage;

	/** @var array  internal default values storage*/
	protected $defaults = array();

	/** @var array  internal column name storage */
	protected $columns = array();

	/**#@+ Record state */
	const STATE_EXISTING = DataStorage::STATE_EXISTING;
	const STATE_NEW = DataStorage::STATE_NEW;
	const STATE_DELETED = DataStorage::STATE_DELETED;
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
		
		$this->storage = new DataStorage($this->getColumnNames(), (array) $input, $this->getDefaultValues(), $state);
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


	/**
	 * Gets record's values in array(column => value)
	 * @retrun array
	 */
	public function getValues() {
		$output = array();
		foreach ($this->getStorage()->values as $field => $value)
			$output[$field] = $this->$field;
		
		return $output;
	}

	public function setValues(array $input) {
		foreach ($input as $field => $value)
			$this->$field = $value;
	}


	/**
	 * Gets record's modified values in array(column => value)
	 * @return array
	 */
	public function getModifiedValues() {
		$output = array();
		foreach ($this->getStorage()->modified as $field => $value)
			$output[$field] = $this->$field;

		return $output;
	}


	/**
	 * Gets record's backup values in array(column => value)
	 * @return array
	 */
	public function getBackupValues() {
		return $this->getStorage()->backup;
	}


	/**
	 * Gets record's default values data of NOT NULL columns in array(column => defaultValue)
	 * @retrun array
	 */
	protected function getDefaultValues() {
		return $this->defaults;
	}
	

	/**
	 * Gets record's internal data stroge object
	 * @retrun DataStorage
	 */
	protected function getStorage() {
		return $this->storage;
	}


	/**
	 * Is record existing?
	 * @return bool
	 */
	public function isRecordExisting() {
		return $this->getStorage()->getState() === self::STATE_EXISTING;
	}


	/**
	 * Is record new?
	 * @return bool
	 */
	public function isRecordNew() {
		return $this->getStorage()->getState() === self::STATE_NEW;
	}


	/**
	 * Is record deleted?
	 * @return bool
	 */
	public function isRecordDeleted() {
		return $this->getStorage()->getState() === self::STATE_DELETED;
	}


	/**
	 * Save the instance.
	 * @return Record
	 */
	public function save() {
		$this->getStorage()->save();
		return $this;
	}


	/**
	 * Destroy the instance.
	 * @return bool  true if Record was destroyed
	 */
	public function destroy() {
		$this->getStorage()->destroy();
		$this->freeze();
	}


	/**
	 * Reset Record's unsaved changes to a similar state as a new Record (thus making all properties non dirty).
	 * @return Record
	 */
	public function discard() {
		$this->getStorage()->discard();
		return $this;
	}


     /**
	  * Makes the object unmodifiable.
	  * @return void
	  */
	public function freeze() {
		$this->getStorage()->freeze();
		parent::freeze();
	}



	/********************* magic getters & setters *********************/



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
			if (isset($this->storage[$name])) {
				$value = $this->storage[$name];
				return $value; // PHP work-around (Only variable references should be returned by reference)
			} else
				throw $e;
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
			if (isset($this->storage[$name]) && $this->storage[$name] !== $value)
				$this->storage[$name] = $value;
			else
				throw $e;
		}
	}


	/**
	 * Is property defined?
	 *
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name) {
		return parent::__isset($name) ? TRUE : isset($this->storage[$name]);
	}


	/**
	 * Unset of property.
	 *
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name) {
		parent::__unset($name);
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