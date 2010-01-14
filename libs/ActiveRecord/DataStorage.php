<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class DataStorage extends Object implements ArrayAccess {

	/** @var array */
	public $originals = array();

	/** @var array */
	public $changes = array();

	public function __construct(array $originals = array(), array $changes = array()) {
		$this->originals = $originals;
		$this->changes = $changes;
	}


	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		if (array_key_exists($name, $this->changes))
			return $this->changes[$name];
		else if (array_key_exists($name, $this->originals))
			return $this->originals[$name];
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
		if (array_key_exists($name, $this->originals)) {
			if ($this->originals[$name] !== $value)
				$this->changes[$name] = $value;
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
		return array_key_exists($name, $this->originals) ? TRUE : parent::__isset($name);
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