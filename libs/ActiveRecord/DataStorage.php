<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 *
 * @property-read array $values
 * @property-read array $modified
 * @property-read array $backup
 * @property-read int $state
 */
class DataStorage extends FreezableObject implements ArrayAccess {

	/** @var array */
	private $values = array();

	/** @var array */
	private $modified = array();

	/** @var array */
	private $backup = array();

	/** @var bool  record state sign */
	private $state;

	/**#@+ storage state */
	const STATE_EXISTING = 'e';
	const STATE_NEW = 'n';
	const STATE_DELETED = 'd';
	/**#@-*/


	public function __construct(array $fields, array $input, array $defaults, $state) {
		if ($state != self::STATE_NEW && $state != self::STATE_EXISTING)
			throw new InvalidArgumentException("Unknow storage state '$state' given");

		$this->state = $state;
		
		// TODO: refaktorovat tuto interní třídu a metody naimplementovat přímo do Recordu
		$result = array();
		$values = $input + $defaults;
		foreach ($fields as $field)
			$result[$field] = isset($values[$field]) ? $values[$field] : NULL;

		$this->values = $this->backup = $result;
		if ($this->state == self::STATE_NEW)
			$this->modified = $this->values;
	}

	private function commit() {
		$this->updating();
		$this->backup = $this->values;
		$this->values = $this->modified + $this->values;
		$this->modified = array();
		$this->state = self::STATE_EXISTING;
	}

	private function rollback() {
		$this->updating();
		$this->values = $this->backup;
		$this->modified = $this->state === self::STATE_NEW ? $this->values : array();

	}

	public function clear() {
		$this->updating();
		$this->modified = array();
		foreach ($this->values as & $v)
			$v = NULL;
		foreach ($this->backup as & $v)
			$v = NULL;
		$this->state = self::STATE_EXISTING;
		$this->freeze();
	}

	public function save() {
		$this->commit();
	}

	public function discard() {
		$this->rollback();
	}

	public function destroy() {
		$this->clear();
		$this->state = self::STATE_DELETED;
	}

	public function getValues() {
		return $this->values;
	}

	public function setValues(array $input) {
		foreach ($input as $field => $value)
			$this[$field] = $value;
		
		return $this->values;
	}

	public function getModified() {
		return $this->modified;
	}

	public function getBackup() {
		return $this->backup;
	}

	public function getState() {
		return $this->state;
	}



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		if (array_key_exists($name, $this->values))
			return $this->values[$name];
		else
			return parent::__get($name);
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
		
		if (array_key_exists($name, $this->values)) {
			if ($this->values[$name] !== $value) {
				$this->values[$name] = $value;
				$this->modified[$name] = $value;
			}
		} else {
			parent::__set($name, $value);
		}
	}


	/**
	 * Is property defined?
	 *
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name) {
		return array_key_exists($name, $this->values) ? TRUE : parent::__isset($name);
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