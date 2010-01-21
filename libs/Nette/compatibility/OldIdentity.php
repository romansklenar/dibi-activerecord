<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Security
 */

/*namespace Nette\Security;*/



/**
 * Old implementation of IIdentity.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 *
 * @property   string $name
 * @property   mixed $id
 * @property   array $roles
 */
class OldIdentity extends /*Nette\*/FreezableObject implements IIdentity
{
	/** @var string */
	private $name;

	/** @var array */
	private $roles;

	/** @var array */
	private $data;


	/**
	 * @param  string  identity name
	 * @param  mixed   roles
	 * @param  array   user data
	 */
	public function __construct($name, $roles = NULL, $data = NULL)
	{
		$this->setName($name);
		$this->setRoles((array) $roles);
		$this->data = (array) $data;
		if (!isset($this->data['id'])) {
			$this->data['id'] = $name;
		}
	}



	/**
	 * Sets the name of user.
	 * @param  string
	 * @return Identity  provides a fluent interface
	 */
	public function setName($name)
	{
		$this->updating();
		$this->name = (string) $name;
		return $this;
	}



	/**
	 * Returns the name of user.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * Sets the ID of user.
	 * @param  mixed
	 * @return Identity  provides a fluent interface
	 */
	public function setId($id)
	{
		$this->updating();
		$this->data['id'] = $id;
		return $this;
	}



	/**
	 * Returns the ID of user.
	 * @return mixed
	 */
	public function getId()
	{
		return isset($this->data['id']) ? $this->data['id'] : NULL;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 * @param  array
	 * @return Identity  provides a fluent interface
	 */
	public function setRoles(array $roles)
	{
		$this->updating();
		$this->roles = $roles;
		return $this;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}



	/**
	 * Returns a user data.
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}



	/**
	 * Sets user data value.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->updating();
		if ($key === 'name' || $key === 'roles') {
			parent::__set($key, $value);

		} else {
			$this->data[$key] = $value;
		}
	}



	/**
	 * Returns user data value.
	 * @param  string  property name
	 * @return mixed
	 */
	public function &__get($key)
	{
		if ($key === 'name' || $key === 'roles') {
			return parent::__get($key);

		} else {
			return $this->data[$key];
		}
	}

}
