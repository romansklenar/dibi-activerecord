<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class RecordHelper {

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct() {
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* values expander *********************/


	/**
	 * @param  Record $record
	 * @param  array  $fields
	 * @return array
	 */
	public static function getValues(Record $record, array $fields = array()) {
		$output = array();
		foreach ($fields as $field)
			$output[$field] = $record->$field;
		return $output;
	}


	/**
	 * @param Record $record
	 * @param array  $input
	 */
	public static function setValues(Record $record, array $input = array()) {
		foreach ($input as $field => $value)
			$this->$field = $value;
	}



	/********************* format helpers *********************/


	/**
	 * Conditions formater.
	 * @param  DibiIndexInfo $primary
	 * @param  array $params
	 * @return array
	 */
	public static function formatConditions(DibiIndexInfo $primary, array $params) {
		foreach ($params as $k => $v)
			if ($v === NULL)
				unset($params[$k]);

		if (count($primary->columns) == 1) {
			return array('%n IN %l', $primary->columns[0]->name, $params);
		} else {
			throw new InvalidStateException("You cannot use this format of conditions when table has primary key composed of more then one column.");
		}
	}



	/********************* record database table reflection *********************/



	/**
	 * Gets record's table reflection object
	 * @param Record $record
	 * @return DibiTableInfo
	 */
	public static function getTableInfo(Record $record) {
		$table = $record->getTableName();
		$cache = self::getCache();
		$key = $table . '.reflection';

		if (isset($cache[$key]))
			return $cache[$key];

		$info = $record->getConnection()->getDatabaseInfo()->getTable($table);
		$info->getColumns();
		$info->getIndexes();
		$info->getForeignKeys();

		$cache->save($key, $info, array(
			'files' => $record->getReflection()->getFileName())
		);
		return $info;
	}


	/**
	 * Gets record's primary key index reflection object
	 * @param Record $record
	 * @return DibiIndexInfo
	 */
	public static function getPrimaryInfo(Record $record) {
		return self::getTableInfo($record)->getPrimaryKey();
	}


	/**
	 * Gets record's table column names.
	 * @param Record $record
	 * @return array
	 */
	public static function getColumnNames(Record $record) {
		$table = $record->getTableName();
		$cache = self::getCache();
		$key = $table . '.columnNames';

		if (isset($cache[$key]))
			return $cache[$key];

		$names = array();
		foreach ($record->getTableInfo()->getColumns() as $column)
			$names[] = $column->name;

		$cache->save($key, $names, array(
			'files' => $record->getReflection()->getFileName())
		);
		return $names;
	}


	/**
	 * Gets record's table columns default values in array(column => defaultValue).
	 * @param Record $record
	 * @return array
	 */
	public static function getColumnDefaults(Record $record) {
		$table = $record->getTableName();
		$cache = self::getCache();
		$key = $table . '.defaults';

		if (isset($cache[$key]))
			return $cache[$key];

		$defaults = array();
		foreach ($record->getTableInfo()->getColumns() as $column)
			$defaults[$column->name] = $column->default;

		$cache->save($key, $defaults, array(
			'files' => $record->getReflection()->getFileName())
		);
		return $defaults;
	}


	/**
	 * Gets record's table column types.
	 * @param Record $record
	 * @return array
	 */
	public static function getColumnTypes(Record $record) {
		$table = $record->getTableName();
		$cache = self::getCache();
		$key = $table . '.types';

		if (isset($cache[$key]))
			return $cache[$key];

		$types = array();
		foreach ($record->getTableInfo()->getColumns() as $column)
			$types[$column->name] = $column->type;

		$cache->save($key, $types, array(
			'files' => $record->getReflection()->getFileName())
		);
		return $types;
	}



	/********************* record assotiations *********************/



	/**
	 * Gets record's assotiations.
	 * @param  Record $record
	 * @return array of Association
	 */
	public static function getAssotiations(Record $record) {
		$reflection = $record->getReflection();
		$class = $reflection->getName();
		$cache = self::getCache();
		$key = $class . '.assotiations';

		if (isset($cache[$key]))
			return $cache[$key];

		$associations = array();
		$arr = $reflection->getAnnotations();

		foreach ($arr as $type => $annotations)
			if (in_array($type, Association::$types))
				foreach ($annotations as $annotation)
					foreach ($annotation->getValues() as $attribute => $referenced) {
						switch ($type) {
							case Association::BELONGS_TO:
								$asc = new BelongsToAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
							case Association::HAS_ONE:
								$asc = new HasOneAssociation($class, $referenced); break;
							case Association::HAS_MANY:
								$asc = new HasManyAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
							case Association::HAS_AND_BELONGS_TO_MANY:
								$asc = new HasAndBelongsToManyAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
						}
						$associations[$type][] = $asc;
					}

		$cache->save($key, $associations, array(
			'files' => array($reflection->getFileName()) // TODO: all ascendants files
		));

		return $associations;
	}



	/********************* cache behaviour *********************/



	/**
	 * @return Cache
	 */
	final public static function getCache() {
		return Environment::getCache('Dibi.ActiveRecod');
	}


	/**
	 * @return void
	 */
	final public static function cleanCache() {
		self::getCache()->clean();
	}
}