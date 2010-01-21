<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class TableHelper {

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct() {
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* table reflection *********************/


	/**
	 * Gets record's table reflection object.
	 *
	 * @param string $class
	 * @return DibiTableInfo
	 */
	public static function getTableInfo($class) {
		$table = $class::getTableName();
		$cache = self::getCache();
		$key = $table . '.reflection';

		if (isset($cache[$key]))
			return $cache[$key];

		$info = $class::getConnection()->getDatabaseInfo()->getTable($table);
		$info->getColumns();
		$info->getIndexes();
		//$info->getForeignKeys(); // not supported by dibi yet

		$rc = new ReflectionClass($class);
		$cache->save($key, $info, array(
			'files' => $rc->getFileName()
		));
		return $info;
	}


	/**
	 * Gets record's primary key index reflection object.
	 *
	 * @param string $class
	 * @return DibiIndexInfo
	 */
	public static function getPrimaryInfo($class) {
		$primary = self::getTableInfo($class)->getPrimaryKey();

		if ($primary instanceof DibiIndexInfo)
			return $primary;
		else
			throw new InvalidStateException("Unable to detect primay key index of table '" . $class::getTableName()
				. "'. You can try manually define primary key column(s) to $class::\$primary static variable.");

	}


	/**
	 * Gets record's table column names.
	 *
	 * @param string $class
	 * @return array
	 */
	public static function getColumnNames($class) {
		$table = $class::getTableName();
		$cache = self::getCache();
		$key = $table . '.columnNames';

		if (isset($cache[$key]))
			return $cache[$key];

		$names = array();
		foreach ($class::getTableInfo()->getColumns() as $column)
			$names[] = $column->name;

		$rc = new ClassReflection($class);
		$cache->save($key, $names, array(
			'files' => $rc->getFileName()
		));
		return $names;
	}


	/**
	 * Gets record's table columns default values in array(column => default value).
	 *
	 * @param string $class
	 * @return array
	 */
	public static function getColumnDefaults($class) {
		$table = $class::getTableName();
		$cache = self::getCache();
		$key = $table . '.defaults';

		if (isset($cache[$key]))
			return $cache[$key];

		$defaults = array();
		foreach ($class::getTableInfo()->getColumns() as $column)
			$defaults[$column->name] = $column->default;

		$rc = new ClassReflection($class);
		$cache->save($key, $defaults, array(
			'files' => $rc->getFileName()
		));
		return $defaults;
	}


	/**
	 * Gets record's table column types.
	 *
	 * @param string $class
	 * @return array
	 */
	public static function getColumnTypes($class) {
		$table = $class::getTableName();
		$cache = self::getCache();
		$key = $table . '.types';

		if (isset($cache[$key]))
			return $cache[$key];

		$types = array();
		foreach ($class::getTableInfo()->getColumns() as $column)
			$types[$column->name] = $column->type;

		$rc = new ClassReflection($class);
		$cache->save($key, $types, array(
			'files' => $rc->getFileName()
		));
		return $types;
	}


	/**
	 * Is primary key index AI?
	 *
	 * @param DibiIndexInfo $index
	 * @return bool
	 */
	public static function isPrimaryAutoIncrement(DibiIndexInfo $index) {
		return count($index->columns) == 1 && $index->columns[0]->isAutoIncrement();
	}


	/**
	 * Is primary key index composed from one column?
	 *
	 * @param DibiIndexInfo $index
	 * @return bool
	 */
	public static function isPrimarySingle(DibiIndexInfo $index) {
		return count($index->columns) == 1;
	}


	/**
	 * Gets name of primary key column(s).
	 * 
	 * @param DibiIndexInfo $index
	 * @return array|string
	 */
	public static function getPrimaryKey(DibiIndexInfo $index) {
		$primary = array();
		foreach ($index->getColumns() as $column)
			$primary[] = $column->getName();
		return count($primary) == 1 ? $primary[0] : $primary;
	}



	/********************* cache behaviour *********************/



	/**
	 * @return Cache
	 */
	final public static function getCache() {
		return RecordHelper::getCache();
	}
}