<?php

require_once __DIR__ . '/compatibility.php';


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class ActiveRecord extends Record {

	/** @var string  table name, if not set uses Inflector::tableize() to detect table name */
	protected static $table;

	/** @var string|array  primary key name, if not set uses DibiTableInfo::getPrimaryKey() to detect primary key(s) */
	protected static $primary;

	/** @var string  foreign key mask, if not set uses Inflector::foreignKey() to detect foreign key names */
	protected static $foreing; // i.e. '%table%Id', '%table%_id', '%table%_%primary%', '%primary%'

	/** @var string  used connection name */
	protected static $connection = Mapper::DEFAULT_CONNECTION;

	/** @var string  used mapper class */
	protected static $mapper = self::DEFAULT_MAPPER;

	/** @var array  detected primary key name and table name cache */
	private static $register = array();

	/** @var IValidator */
	private static $validator;

	/** @var Storage */
	private $values;

	/** @var Storage */
	private $dirty;

	/** @var bool  record state sign */
	private $state;

	/**#@+ Configuration options */
	const DEFAULT_MAPPER = 'ActiveMapper';
	/**#@-*/

	/**#@+ state */
	const STATE_INICIALIZING = '%I';
	const STATE_EXISTING = '%E';
	const STATE_NEW = '%N';
	const STATE_DELETED = '%D';
	/**#@-*/


	/**
	 * ActiveRecord constructor.
	 * @param ArrayObject|array $input
	 * @param int $state  does data physically exists in database?
	 */
	public function __construct($input = array(), $state = NULL) {
		if (!is_array($input) && !$input instanceof ArrayObject)
			throw new InvalidArgumentException("Provided input is not array or ArrayObject, '" . gettype($input) . "' given.");

		if ($state === NULL)
			$state = $this->detectState((array) $input);

		if ($state !== self::STATE_NEW && $state !== self::STATE_EXISTING)
			throw new InvalidArgumentException("Unknow record state '$state' given.");

		$this->state = $state;
		$this->values = new Storage;
		$this->dirty = new Storage;

		$values = (array) $input + $this->getDefaults();
		$this->setValues($values);

		$this->values = $this->dirty;
		if ($this->isExistingRecord())
			$this->dirty = new Storage;
	}


	public function  __destruct() {
		// TODO: rollback všech nedokončených transakcí
	}



	/********************* state stuff *********************/



	/**
	 * Detects record's state.
	 * @param array $input
	 * @return int
	 */
	private function detectState(array $input) {
		$state = count(self::getColumnNames()) !== count($input) || count($input) == 0 ? self::STATE_NEW : self::STATE_EXISTING;

		if ($state == self::STATE_EXISTING) {
			$primary = is_array(self::getPrimaryKey()) ? self::getPrimaryKey() : array(self::getPrimaryKey());
			foreach ($primary as $key) {
				if (isset($input[$key])) {
					if ($input[$key] === NULL)
						return self::STATE_NEW;
				} else {
					return self::STATE_NEW;
				}
			}
		}

		if ($state !== self::STATE_NEW && $state !== self::STATE_EXISTING)
			throw new InvalidArgumentException("Unable to detect record state.");

		return $state;
	}


	/**
	 * Is record existing?
	 * @return bool
	 */
	public function isExistingRecord() {
		return $this->state === self::STATE_EXISTING;
	}


	/**
	 * Is record new?
	 * @return bool
	 */
	public function isNewRecord() {
		return $this->state === self::STATE_NEW;
	}


	/**
	 * Is record deleted?
	 * @return bool
	 */
	public function isDeletedRecord() {
		return $this->state === self::STATE_DELETED;
	}



	/********************* connection stuff *********************/



	/**
	 * Gets database connection
	 * @return DibiConnection
	 */
	public static function getConnection() {
		return ActiveMapper::getConnection(self::getConnectionName());
	}


	/**
	 * Gets record's connection name
	 * @return string
	 */
	private static function getConnectionName() {
		return static::$connection;
	}


	/**
	 * Gets mapper class name.
	 * @return string
	 */
	private static function getMapper() {
		return static::$mapper;
	}


	/**
	 * DibiDataSource finder factory.
	 * @return DibiDataSource
	 */
	public static function getDataSource() {
		return self::getConnection()->dataSource(self::getTableName());
	}



	/********************* database stuff *********************/



	/**
	 * Gets record's table name
	 * @return string
	 */
	public static function getTableName() {
		if (isset(static::$table) && !empty(static::$table)) {
			return static::$table;

		} else {
			$class = self::getClass();
			if (!isset(self::$register[$class]['table']))
				self::$register[$class]['table'] = Inflector::tableize($class);
			return self::$register[$class]['table'];
		}
	}


	/**
	 * Gets table's reflection object
	 * @return DibiTableInfo
	 */
	public static function getTableInfo() {
		return TableHelper::getTableInfo(self::getClass());
	}


	/**
	 * Does record's table exists in database?
	 * @return bool
	 */
	public static function tableExists() {
		return self::getConnection()->getDatabaseInfo()->hasTable(self::getTableName());
	}


	/**
	 * Gets record's primary key column(s) name
	 * @return string|array
	 */
	public static function getPrimaryKey() {
		if (isset(static::$primary) && !empty(static::$primary)) {
			return static::$primary;

		} else {
			$class = self::getClass();
			if (!isset(self::$register[$class]['primary']))
				self::$register[$class]['primary'] = TableHelper::getPrimaryKey(TableHelper::getPrimaryInfo($class));
			return self::$register[$class]['primary'];
		}
	}


	/**
	 * Gets table's primary key index reflection object
	 * @return DibiIndexInfo
	 */
	public static function getPrimaryInfo() {
		if (isset(static::$primary) && !empty(static::$primary)) {
			return self::generatePrimaryInfo();
		} else {
			return TableHelper::getPrimaryInfo(self::getClass());
		}
	}


	/**
	 * Generates virtual DibiIndexInfo object.
	 * Hook for database which do not support index reflection in specific DibiDriver
	 * @return DibiIndexInfo
	 */
	private static function generatePrimaryInfo() {
		$primary = self::getPrimaryKey();
		$info = array(
			'name' => self::getTableName() . '_primary',
			'columns' => is_array($primary) ? $primary : array($primary),
			'unique' => FALSE,
			'primary' => TRUE,
		);

		foreach ($info['columns'] as $key => $name) {
			$info['columns'][$key] = self::getTableInfo()->getColumn($name);
			if ($info['columns'][$key]->isAutoIncrement())
				$info['unique'] = TRUE;
		}
		return new DibiIndexInfo($info);
	}


	/**
	 * Gets record's foreign key.
	 * @return string
	 */
	public static function getForeignKey() {
		if (isset(static::$foreing) && !empty(static::$foreing)) {
			return str_replace(
				array('%table%', '%primary%'),
				array(self::getTableName(), self::getPrimaryKey()),
				static::$foreing
			);

		} else {
			$class = self::getClass();
			if (!isset(self::$register[$class]['foreign']))
				self::$register[$class]['foreign'] = Inflector::foreignKey($class);
			return self::$register[$class]['foreign'];
		}
	}


	/**
	 * Returns record's columns reflection objects
	 * @retrun array
	 */
	public static function getColumns() {
		return self::getTableInfo()->getColumns();
	}


	/**
	 * Has record's table given column?
	 * @retrun array
	 */
	private static function hasColumn($name) {
		return self::getTableInfo()->hasColumn($name);
	}


	/**
	 * Returns record's columns names
	 * @retrun array
	 */
	private static function getColumnNames() {
		return TableHelper::getColumnNames(self::getClass());
	}



	/********************* association handling *********************/



	/**
	 * Gets record's associations.
	 * @param string|array $filter
	 * @return array
	 */
	public static function getAssociations($filter = NULL) {
		$asc = RecordHelper::getAssociations(self::getClass());
		if ($filter === NULL)
			return $asc;

		if (is_string($filter))
			$filter = array($filter);

		$arr = array();
		foreach ($asc as $name => $association)
			if (in_array($association->getType(), $filter))
				$arr[] = $association;
		return $arr;
	}


	/**
	 * Has record association to another record?
	 * @param string $name  name of called attribute / related class name
	 * @return bool|Association
	 */
	private function hasAssociation($name) {
		$asc = self::getAssociations();
		$exists = array_key_exists($name, $asc);
		if ($exists || $this->state === self::STATE_INICIALIZING)
			return $exists;

		foreach ($asc as $association)
			if ($association->isInRelation(Inflector::classify($name)))
				return TRUE;
		return FALSE;
	}


	/**
	 * Gets association to another record
	 * @param string $name  name of called attribute / related class name
	 * @return Association
	 */
	private function getAssociation($name) {
		$asc = self::getAssociations();
		if ($this->hasAssociation($name))
			return $asc[$name];
		else
			throw new ActiveRecordException("Asscociation to '" . Inflector::classify($name) . "' not founded.");
/*
		// deprecated
		$asc = $this->hasAssociation($name);
		if ($asc instanceof Association)
			return $asc;
		else
			throw new ActiveRecordException("Asscociation to '$name' not founded.");
*/
	}



	/********************* attributes handling *********************/



	/**
	 * Returns list of attributes.
	 * @return array
	 */
	public static function getAttributes() {
		return array_merge(self::getColumnNames(), array_keys(self::getAssociations()));
	}


	/**
	 * Is attribute defined?
	 * @bool
	 */
	protected function hasAttribute($name) {
		return in_array($name, self::getAttributes());
	}


	/**
	 * Returns attribute value.
	 * @param  string $offset  attribute name
	 * @return mixed           attribute value
	 * @throws MemberAccessException if the attribute is not defined.
	 */
	protected function getAttribute($name) {
		if ($this->hasAssociation($name)) {
			if (array_key_exists($name, $this->dirty))
				return $this->dirty->$name;
			else if (array_key_exists($name, $this->values))
				return $this->values->$name;
			else // lazy load
				return $this->values->$name = $this->getAssociation($name)->retreiveReferenced($this);

		} else if ($this->hasColumn($name)) {
			if (array_key_exists($name, $this->dirty))
				$value = $this->dirty->$name;
			else if (array_key_exists($name, $this->values))
				$value = $this->values->$name;
			else // not inicialized yet
				return NULL;
			return $this->typeCast($name, $value);

		} else {
			throw MemberAccessException("Unknown record attribute $this->class::\$$name.");
		}
	}


	/**
	 * Sets value of a attribute.
	 * @param  string $name  attribute name
	 * @param  mixed  $value   attribute value
	 * @return void
	 * @throws MemberAccessException if the attribute is not defined or is read-only
	 */
	protected function setAttribute($name, $value) {
		if ($this->hasAssociation($name)) {
			$current = $this->getAttribute($name);
			$asc = $this->getAssociation($name);
			if ($asc->typeCheck($value)) {
				$this->dirty->$name = $asc->linkWithReferenced($this, $value);
			} else {
				$many = $asc->type == Association::HAS_MANY || $asc->type == Association::HAS_AND_BELONGS_TO_MANY;
				$type = $many ? "collection of $asc->referenced objects" : "object of $asc->referenced";
				$class = get_class($value);
				throw new InvalidArgumentException("Cannot assign object of $class, $type expected.");
			}

		} else if ($this->hasColumn($name)) {
			$current = $this->getAttribute($name);
			if ($current != $value)
				$this->dirty->$name = $this->typeCast($name, $value);

		} else {
			throw MemberAccessException("Unknown record attribute $this->class::\$$name.");
		}
	}


	private function typeCast($name, $value) {
		if ($value === NULL || $value instanceof ActiveRecord || $value instanceof ActiveRecordCollection)
			return $value;

		switch ($this->types[$name]) {
			case dibi::TEXT: $value = (string) $value; break;
			case dibi::BOOL: $value = ((bool) $value) && $value !== 'f' && $value !== 'F'; break;
			case dibi::INTEGER: $value = (int) $value; break;
			case dibi::FLOAT: $value = (float) $value; break;
			case dibi::DATE:
			case dibi::TIME:
			case dibi::DATETIME:
				if ($value instanceof DateTime)
					return $value;
				else if ((int) $value === 0) // '', NULL, FALSE, '0000-00-00', ...
					return NULL;
				else
					$value = new DateTime(is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value);
				break;

			case dibi::BINARY:
			default: break;
		}
		return $value;
	}



	/********************* values stuff *********************/



	/**
	 * Gets table's column types in array(column => type)
	 * @return array
	 */
	public static function getTypes() {
		return TableHelper::getColumnTypes(self::getClass());
	}


	/**
	 * Gets record's columns values in array(column => value)
	 * @retrun array
	 */
	public function getValues() {
		return RecordHelper::getValues($this, self::getColumnNames());
	}


	/**
	 * Sets record's columns values in array(column => value)
	 * @param array $input
	 */
	public function setValues(array $input) {
		RecordHelper::setValues($this, $input);
	}


	/**
	 * Returns map of columns default values (column => default value)
	 * @retrun array
	 */
	private static function getDefaults() {
		return TableHelper::getColumnDefaults(self::getClass());
	}


	/**
	 * Returns map of changed attributes (attr => new value)
	 * @return array
	 */
	public function getChanges() {
		return clone $this->dirty;
	}


	/**
	 * Returns map of original attributes (attr => original value)
	 * @return array
	 */
	public function getOriginals() {
		return clone $this->values;
	}


	/**
	 * Returns value of changed attribute
	 * @param string $attr
	 * @return array
	 */
	public function getChange($attr) {
		return $this->dirty->$attr;
	}


	/**
	 * Returns value of original attribute
	 * @param string $attr
	 * @return array
	 */
	public function getOriginal($attr) {
		return $this->values->$attr;
	}



	/********************* validation *********************/



	/**
	 * Gets record's validator.
	 * @return Validator
	 */
	protected function getValidator() {
		throw new NotImplementedException;

		if (static::$validator === NULL)
			static::$validator = new Validator;

		return static::$validator;
	}


	/**
	 * @return void
	 */
	public function validate() {
		throw new NotImplementedException;
		$this->getValidator()->validate($this);
	}


	/**
	 * @return bool
	 */
	public function isValid() {
		throw new NotImplementedException;

		try {
			$this->validate();
			return TRUE;

		} catch (ValdationException $e) {
			return FALSE;
		}
	}



	/********************* executors *********************/



	/**
	 * Checks if the Record has unsaved changes.
	 * @return bool
	 */
	public function isDirty() {
		return (bool) count($this->dirty) || $this->isNewRecord();
	}


	/**
	 * Discards Record's unsaved changes to a similar state as was initialized (thus making all properties non dirty).
	 * @return void
	 */
	public function discard() {
		$this->updating();
		$this->dirty = $this->isNewRecord() ? $this->values : new Storage;
		foreach ($this->dirty as $unsaved)
			if ($unsaved->isDirty())
				$unsaved->discard();
	}


	/**
	 * Save the instance and loaded, dirty associations to the repository.
	 * @return ActiveRecord
	 */
	public function save() {
		if ($this->isDirty()) {
			try {
				$mapper = self::getMapper();
				$mapper::save($this);
				
				foreach ($this->dirty as $unsaved)
					if ($unsaved instanceof ActiveRecord || $unsaved instanceof ActiveRecordCollection)
						$unsaved->save();

				$this->updating();
				$this->values = new Storage($this->getValues());
				$this->dirty = new Storage();
				$this->state = self::STATE_EXISTING;

			} catch (DibiException $e) {
				throw new ActiveRecordException("Unable to save record.", 500, $e);
			}
		}
	}


	/**
	 * Destroy the instance, remove it from the repository.
	 * @return bool  true if Record was destroyed
	 */
	public function destroy() {
		try {
			$mapper = self::getMapper();
			$deleted = (bool) $mapper::delete($this);

			$this->updating();
			$this->dirty = new Storage();
			foreach ($this->values as & $v)
				$v = NULL;
			$this->state = self::STATE_DELETED;
			$this->freeze();

		} catch (DibiException $e) {
				throw new ActiveRecordException("Unable to destroy record.", 500, $e);
		}

		return $deleted;
	}


	/**
	 * Active Record factory
	 * @return ActiveRecord
	 */
	public static function create($data = array()) {
		$record = new static($data, self::STATE_NEW);
		$record->save();
		return $record;
	}



	/********************* counting *********************/



	/**
	 * Counter.
	 * @param array $where
	 * @param array $limit
	 * @param array $offset
	 * @return int
	 */
	public static function count($where = array(), $limit = NULL, $offset = NULL) {
		if (!is_array($where) && (is_numeric($where) || (is_string($where) && str_word_count($where) == 1)))
			$where = array(RecordHelper::formatArguments(self::getPrimaryInfo(), func_get_args())); // intentionally not getPrimaryInfo() from helper

		$mapper = self::getMapper();
		return $mapper::find(self::getClass(), array('where' => $where, 'limit' => $limit, 'offset' => $offset), IMapper::ALL)->count();
	}


	/**
	 * Counter.
	 * @param string $column
	 * @return float|int
	 */
	public static function avarage($column) {
		return self::getConnection()->query('SELECT A(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Counter.
	 * @param string $column
	 * @return float|int
	 */
	public static function minimum($column) {
		return self::getConnection()->query('SELECT MIN(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Counter.
	 * @param string $column
	 * @return float|int
	 */
	public static function maximum($column) {
		return self::getConnection()->query('SELECT MAX(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Counter.
	 * @param string $column
	 * @return float|int
	 */
	public static function sum($column) {
		return self::getConnection()->query('SELECT SUM(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}



	/********************* finders *********************/



	/**
	 * Django-like alias to find().
	 * @return ActiveRecordCollection
	 */
	public static function objects() {
		return self::findAll();
	}


	/**
	 * Static finder.
	 * @param array $where
	 * @param array $order
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public static function find($where = array(), $order = array()) {
		$mapper = self::getMapper();

		if (!is_array($where) && (is_numeric($where) || (is_string($where) && str_word_count($where) == 1))) {
			$params = func_get_args();
			$where = RecordHelper::formatArguments(self::getPrimaryInfo(), $params); // intentionally not getPrimaryInfo() from helper
			return $mapper::find(self::getClass(), array('where' => array($where)), count($params) == 1 ? IMapper::FIRST : IMapper::ALL);

		} else {
			return $mapper::find(self::getClass(), array('where' => $where, 'order' => $order), IMapper::FIRST);
		}
	}


	/**
	 * Static finder.
	 * @param array $where
	 * @param array $order
	 * @param array $limit
	 * @param array $offset
	 * @return ActiveRecordCollection|NULL
	 */
	public static function findAll($where = array(), $order = array(), $limit = NULL, $offset = NULL) {
		$mapper = self::getMapper();
		return $mapper::find(self::getClass(), array('where' => $where, 'order' => $order, 'limit' => $limit, 'offset' => $offset), IMapper::ALL);
	}


	private static function findBy($where = array(), $scope = IMapper::FIRST) {
		foreach ($where as $key => $value)
			if (is_array($value)) {
				unset($where[$key]);
				$where[] = array('%n IN %l', $key, $value);
			}

		$mapper = self::getMapper();
		return $scope == IMapper::FIRST ? self::find($where) : self::findAll($where);
	}


	/**
	 * Static Magic find.
	 * - $col = Page::findAllByUrl('about-us');
	 * - $rec = Page::findByCategoryIdAndVisibility(5, TRUE);
	 * - $rec = User::findByNameAndLogin('John', 'john007');
	 * - $rec = Product::findByCategory(3);
	 *
	 * @param string $name
	 * @param array  $args
	 * @return ActiveRecordCollection|ActiveRecord|NULL
	 */
	public static function __callStatic($name, $args) {
		if (strncmp($name, 'findBy', 6) === 0) { // single record
			$method = 'find';
			$name = substr($name, 6);

		} elseif (strncmp($name, 'findAllBy', 9) === 0) { // record collection
			$method = 'findAll';
			$name = substr($name, 9);

		} else {
			return parent::__callStatic($name, $args);
		}

		// ProductIdAndTitle -> array('productId', 'title')
		$parts = array_map('lcfirst', explode('And', $name));

		if (count($parts) !== count($args)) {
			throw new InvalidArgumentException("Magic find expects " . count($parts) . " parameters, but " . count($args) . " was given.");
		}

		return self::findBy(array_combine($parts, $args), $method == 'find' ? IMapper::FIRST : IMapper::ALL);
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
		try {
			return parent::__call($name, $args);

		} catch (MemberAccessException $e) {
			return self::__callStatic($name, $args);
		}
	}



	/********************* magic getters & setters *********************/



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	final public function &__get($name) {
		try {
			$value = ObjectMixin::get($this, $name);
			return $value;

		} catch(MemberAccessException $e) {
			if ($this->hasAttribute($name)) {
				$value = $this->getAttribute($name);
				return $value;

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
	final public function __set($name, $value) {
		$this->updating();

		try {
			ObjectMixin::set($this, $name, $value);

		} catch(MemberAccessException $e) {
			if ($this->hasAttribute($name)) {
				$this->setAttribute($name, $value);

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
	final public function __isset($name) {
		return ObjectMixin::has($this, $name) ? TRUE : ($this->hasAssociation($name) || $this->hasAttribute($name) ? TRUE : FALSE);
	}


	/**
	 * Unset of property.
	 *
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	final public function __unset($name) {
		throw new NotSupportedException("Cannot unset the property $this->class::\$$name.");
	}
}