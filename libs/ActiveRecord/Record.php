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
abstract class Record implements ArrayAccess {

	/** @var bool */
	private $frozen = FALSE;



	/********************* freezable *********************/



	/**
	 * Makes the object unmodifiable.
	 * 
	 * @return void
	 */
	public function freeze() {
		$this->frozen = TRUE;
	}


	/**
	 * Is the object unmodifiable?
	 *
	 * @return bool
	 */
	final public function isFrozen() {
		return $this->frozen;
	}


	/**
	 * Creates a modifiable clone of the object.
	 *
	 * @return void
	 */
	public function __clone() {
		$this->frozen = FALSE;
	}


	/**
	 * @return void
	 */
	protected function updating() {
		if ($this->frozen) {
			throw new InvalidStateException("Cannot modify a frozen object $this->class.");
		}
	}



	/********************* Nette\Object port *********************/



	/**
	 * Returns the name of the class of this object.
	 *
	 * @return string
	 */
	public static function getClass() {
		return get_called_class();
	}


	/**
	 * Access to reflection.
	 *
	 * @return ClassReflection
	 */
	public static function getReflection() {
		return new ClassReflection(self::getClass());
	}


	/**
	 * Call to undefined method.
	 *
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args) {
		return ObjectMixin::call($this, $name, $args);
	}


	/**
	 * Call to undefined static method.
	 *
	 * @param  string  method name (in lower case!)
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public static function __callStatic($name, $args) {
		$class = self::getClass();
		throw new MemberAccessException("Call to undefined static method $class::$name().");
	}


	/**
	 * Adding method to class.
	 *
	 * @param  string  method name
	 * @param  mixed   callback or closure
	 * @return mixed
	 */
	public static function extensionMethod($name, $callback = NULL) {
		if (strpos($name, '::') === FALSE)
			$class = get_called_class();
		else
			list($class, $name) = explode('::', $name);

		$class = new ClassReflection($class);
		if ($callback === NULL)
			return $class->getExtensionMethod($name);
		else
			$class->setExtensionMethod($name, $callback);
	}


	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		return ObjectMixin::get($this, $name);
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
		return ObjectMixin::set($this, $name, $value);
	}



	/**
	 * Is property defined?
	 *
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name) {
		return ObjectMixin::has($this, $name);
	}



	/**
	 * Access to undeclared property.
	 *
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name) {
		throw new MemberAccessException("Cannot unset the property $this->class::\$$name.");
	}



	/********************* attributes handling *********************/



	/**
	 * Returns attribute value.
	 *
	 * @param  string $offset  attribute name
	 * @return mixed           attribute value
	 * @throws MemberAccessException if the attribute is not defined.
	 */
	abstract protected function getAttribute($name);


	/**
	 * Sets value of a attribute.
	 *
	 * @param  string $name  attribute name
	 * @param  mixed  $value   attribute value
	 * @return void
	 * @throws MemberAccessException if the attribute is not defined or is read-only
	 */
	abstract protected function setAttribute($name, $value);



	/********************* interface ArrayAccess *********************/



	/**
	 * Returns attribute value. Do not call directly.
	 *
	 * @param  string $name  attribute name
	 * @return mixed           attribute value
	 * @throws MemberAccessException if the attribute is not defined.
	 */
	final public function offsetGet($name) {
		return $this->getAttribute($name);
	}


	/**
	 * Sets value of an attribute. Do not call directly.
	 *
	 * @param  string $name  attribute name
	 * @param  mixed  $value   attribute value
	 * @return void
	 * @throws MemberAccessException if the attribute is not defined or is read-only
	 */
	final public function offsetSet($name, $value) {
		$this->setAttribute($name, $value);
	}


	/**
	 * Is attribute defined?
	 *
	 * @param  string $name  attribute name
	 * @return bool
	 */
	final public function offsetExists($name) {
		return $this->hasAttribute($name);
	}


	/**
	 * Unset of attribute.
	 *
	 * @param  string $name  attribute name
	 * @return void
	 * @throws MemberAccessException
	 */
	final public function offsetUnset($name) {
		throw new NotSupportedException("Cannot unset the attribute $name.");
	}
}