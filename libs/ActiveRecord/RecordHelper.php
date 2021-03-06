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
	 * Values expander.
	 *
	 * @param  Record $record
	 * @param  array  $attrs  filtered attributes
	 * @return array
	 */
	public static function getValues(Record $record, array $attrs = array()) {
		$attrs = empty($attrs) ? $record->attributes : $attrs;
		$output = array();
		foreach ($attrs as $field)
			$output[$field] = $record->$field;
		return $output;
	}


	/**
	 * Values expander.
	 * 
	 * @param Record $record
	 * @param array  $values
	 */
	public static function setValues(Record $record, array $values = array()) {
		foreach ($values as $attr => $value)
			$record->$attr = $value;
	}



	/********************* format helpers *********************/


	/**
	 * Conditions formater.
	 *
	 * @param  DibiIndexInfo $primary
	 * @param  array $params
	 * @return array
	 */
	public static function formatArguments(DibiIndexInfo $primary, array $params) {
		foreach ($params as $k => $v)
			if ($v === NULL)
				unset($params[$k]);

		if (count($primary->columns) == 1)
			return array('%n IN %l', $primary->columns[0]->name, $params);
		else
			throw new InvalidStateException("You cannot use this format of arguments when table has primary key composed of more then one column.");
	}


	/**
	 * Returns formated condition for record's primary key(s).
	 *
	 * @return array
	 */
	public static function formatPrimaryKey(Record $record) { // getPrimaryCondition
		$cond = array();
		foreach	($record->primaryInfo->columns as $column)
			$cond[$column->name . '%' . $column->type] = $record->originals[$column->name];

		return $cond;
	}


	/**
	 * Returns formated condition for record's foreign key.
	 *
	 * @return array
	 */
	public static function formatForeignKey(Record $record) {
		$primary = $record->primaryInfo;
		if (!self::isPrimarySingle($record->primaryKey))
			throw new InvalidStateException("You cannot use this format of conditions when table has primary key composed from more then one column.");

		$cond = array();
		$cond[$record->foreignKey . '%' . $primary->columns[0]->type] = $record->originals[$record->primaryKey];
		return $cond;
	}


	/**
	 * Returns formated condition for record's changed attributes.
	 *
	 * @return array
	 */
	public static function formatChanges(Record $record) {
		$class = $record->class;
		$attrs = array_keys((array) $record->changes);

		if ($record->isNewRecord() && TableHelper::isPrimaryAutoIncrement($record->primaryInfo))
			if (in_array($record->primaryKey, $attrs))
				unset($attrs[array_search($record->primaryKey, $attrs)]);

		$cond = array();
		foreach ($attrs as $attr)
			if ($record->hasColumn($attr))
				$cond[$attr . '%' . $record->types[$attr]] = $record->$attr;
		return $cond;
	}



	/********************* record database table reflection *********************/



	/**
	 * Gets name of primary key.
	 *
	 * @param DibiIndexInfo|array|string $primary
	 * @return array|string
	 */
	public static function getPrimaryKey($primary) {
		if ($primary instanceof DibiIndexInfo)
			return TableHelper::getPrimaryKey($primary);
		else if (is_array($primary))
			return $primary[0];
		else if (is_string($primary))
			return $primary;
		else
			throw new InvalidArgumentException("Unknown primary structure given.");
	}

	
	/**
	 * Is record's primary key composed from one column?
	 *
	 * @param string|array|DibiIndexInfo $primary
	 * @return bool
	 */
	public static function isPrimarySingle($primary) {
		if ($primary instanceof DibiIndexInfo)
			return TableHelper::isPrimarySingle($primary);
		else if (is_array($primary))
			return count($primary) == 1;
		else if (is_string($primary))
			return TRUE;
		else
			throw new InvalidArgumentException("Unknown primary structure given.");
	}



	/********************* record associations *********************/



	/**
	 * Gets record's associations.
	 * 
	 * @param  string $class
	 * @return array of Association
	 */
	public static function getAssociations($class) {
		$rc = new /*Nette\Reflection\*/ClassReflection($class);
		$cache = self::getCache();
		$key = $class . '.associations';

		if (isset($cache[$key]))
			return $cache[$key];

		$associations = array();
		$arr = $rc->getAnnotations();

		foreach ($arr as $type => $annotations)
			if (in_array($type, Association::$types))
				foreach ($annotations as $annotation)
					foreach ($annotation->getValues() as $attribute => $referenced) {
						switch ($type) {
							case Association::BELONGS_TO: $asc = new BelongsToAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
							case Association::HAS_ONE: $asc = new HasOneAssociation($class, $referenced); break;
							case Association::HAS_MANY: $asc = new HasManyAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
							case Association::HAS_AND_BELONGS_TO_MANY: $asc = new HasAndBelongsToManyAssociation($class, $referenced, is_numeric($attribute) ? NULL : $attribute); break;
						}
						$associations[$asc->getAttribute()] = $asc;
					}

		$cache->save($key, $associations, array(
			'files' => array($rc->getFileName()) // TODO: all ascendants files
		));

		return $associations;
	}



	/********************* record validation *********************/



	/**
	 * Gets record's rules.
	 *
	 * @param  string $class
	 * @return Form
	 */
	public static function getRules($class) {
		throw new NotImplementedException;
	}


	/**
	 * Form rules generator.
	 *
	 * @param ActiveRecord $record
	 * @param Form $form
	 * @return Form
	 */
	public static function generateRules(ActiveRecord $record, Form $form) {
		throw new NotImplementedException;
	}



	/********************* cache behaviour *********************/



	/**
	 * @return Cache
	 */
	final public static function getCache() {
		return /*Nette\*/Environment::getCache('Dibi.ActiveRecord');
	}


	/**
	 * @return void
	 */
	final public static function cleanCache() {
		self::getCache()->clean();
	}
}
