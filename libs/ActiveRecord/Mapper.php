<?php

/**
 * Base mapper class by pattern Table Data Gateway.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class Mapper extends Object implements IMapper {

	const DEFAULT_CONNECTION = '#M';
	
	
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct() {
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}


	/**
	 * Creates a new DibiConnection object and connects it to specified database.
	 * 
	 * @param  array|string|ArrayObject $config  connection parameters
	 * @param  string $name       connection name
	 * @return DibiConnection
	 * @throws DibiException
	 */
	public static function connect($config = array(), $name = self::DEFAULT_CONNECTION) {
		return dibi::connect($config, $name);
	}


	/**
	 * Disconnects from database (destroys DibiConnection object).
	 *
	 * @param  string $name  connection name
	 * @return void
	 */
	public static function disconnect($name = self::DEFAULT_CONNECTION) {
		$connection = self::getConnection($name);
		$connection->disconnect();
		unset($connection);
	}


	/**
	 * Returns TRUE when connection was established.
	 *
	 * @param  string $name  connection name
	 * @return bool
	 */
	public static function isConnected($name = self::DEFAULT_CONNECTION) {
		return self::getConnection($name)->isConnected();
	}


	/**
	 * Retrieve active connection.
	 *
	 * @param  string $name   connection registy name
	 * @return DibiConnection
	 * @throws DibiException
	 */
	public static function getConnection($name = self::DEFAULT_CONNECTION) {
		return dibi::getConnection($name);
	}



	/********************* IMapper interface *********************/



	abstract public function find($class, $options = array());

	abstract public function save(Record $record);

	abstract public function update(Record $record);

	abstract public function insert(Record $record);

	abstract public function delete(Record $record);

}