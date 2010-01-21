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
	protected static $validator;

	/** @var Storage  internal data storage */
	private $values;

	/** @var Storage  internal dirty data storage */
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
	 * Object constructor.
	 *
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


	/**
	 * Object destructor.
	 */
	public function  __destruct() {
		// TODO: rollback all incomplete transactions
	}



	/********************* state stuff *********************/



	/**
	 * Try to detects state of this object.
	 *
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
	 * Returns true if this object has been saved yet — that is, a record for the object exists in repository.
	 *
	 * @return bool
	 */
	public function isExistingRecord() {
		return $this->state === self::STATE_EXISTING;
	}


	/**
	 * Returns true if this object hasn't been saved yet — that is, a record for the object doesn't exist yet.
	 *
	 * @return bool
	 */
	public function isNewRecord() {
		return $this->state === self::STATE_NEW;
	}


	/**
	 * Returns true if this object has been deleted yet — that is, a record for the object was deleted from repository.
	 *
	 * @return bool
	 */
	public function isDeletedRecord() {
		return $this->state === self::STATE_DELETED;
	}



	/********************* connection stuff *********************/



	/**
	 * Returns the connection associated with the class.
	 *
	 * @return DibiConnection
	 */
	public static function getConnection() {
		return ActiveMapper::getConnection(self::getConnectionName());
	}


	/**
	 * Returns the connection name associated with the class.
	 *
	 * @return string
	 */
	private static function getConnectionName() {
		return static::$connection;
	}


	/**
	 * Returns the mapper class name associated with the class.
	 *
	 * @return string
	 */
	private static function getMapper() {
		return static::$mapper;
	}


	/**
	 * Returns the data source object associated with the class.
	 *
	 * @return DibiDataSource
	 */
	public static function getDataSource() {
		return self::getConnection()->dataSource(self::getTableName());
	}



	/********************* database stuff *********************/



	/**
	 * Defines table name associated with this class — can be overridden in subclasses.
	 *
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
	 * Returns the table reflection object associated with the class.
	 *
	 * @return DibiTableInfo
	 */
	public static function getTableInfo() {
		return TableHelper::getTableInfo(self::getClass());
	}


	/**
	 * Indicates whether the table associated with this class exists.
	 *
	 * @return bool
	 */
	public static function tableExists() {
		return self::getConnection()->getDatabaseInfo()->hasTable(self::getTableName());
	}


	/**
	 * Defines the primary key field — can be overridden in subclasses.
	 *
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
	 * Returns the primary key index reflection object associated with the class.
	 *
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
	 * Generates virtual primary key index reflection object.
	 * Hook for database which do not support index reflection in specific DibiDriver.
	 *
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
	 * Defines the foreign key field name — can be overridden in subclasses.
	 *
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
	 * Returns an array of column reflection objects for the table associated with this class.
	 *
	 * @return array
	 */
	public static function getColumns() {
		return self::getTableInfo()->getColumns();
	}


	/**
	 * Does table associated with this class has given column?
	 *
	 * @param string $name
	 * @return array
	 */
	public function hasColumn($name) {
		return self::getTableInfo()->hasColumn($name);
	}


	/**
	 * Returns an array of column names as strings.
	 *
	 * @return array
	 */
	public static function getColumnNames() {
		return TableHelper::getColumnNames(self::getClass());
	}



	/********************* association handling *********************/



	/**
	 * Returns an array of association objects for the associations of with this class.
	 *
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
	 * Does specified class or attribute has association to this class?
	 *
	 * @param string $name  name of called attribute / related class name
	 * @return bool|Association
	 */
	public function hasAssociation($name) {
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
	 * Returns association object to specified class.
	 *
	 * @param string $name  name of called attribute / related class name
	 * @return Association
	 */
	public function getAssociation($name) {
		$asc = self::getAssociations();
		if ($this->hasAssociation($name)) {
			if (array_key_exists($name, $asc)) {
				return $asc[$name];
			} else {
				foreach ($asc as $association)
					if ($association->isInRelation(Inflector::classify($name)))
						return $association;
			}
		} else {
			throw new ActiveRecordException("Asscociation to '" . Inflector::classify($name) . "' not founded.");
		}
	}



	/********************* attributes handling *********************/



	/**
	 * Returns an array of names for the attributes available on this object.
	 *
	 * @return array
	 */
	public static function getAttributes() {
		return array_merge(self::getColumnNames(), array_keys(self::getAssociations()));
	}


	/**
	 * Is the specified attribute defined on this object?
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function hasAttribute($name) {
		return in_array($name, self::getAttributes());
	}


	/**
	 * Returns value of specified attribute.
	 *
	 * @param  string $name  attribute name
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
	 * Assigns value to specified attribute.
	 *
	 * @param  string $name    attribute name
	 * @param  mixed  $value   attribute value
	 * @return void
	 * @throws MemberAccessException if the attribute is not defined or is read-only
	 */
	protected function setAttribute($name, $value) {
		if ($this->hasAssociation($name)) {
			$current = $this->getAttribute($name);
			$asc = $this->getAssociation($name);
			if ($asc->typeCheck($value)) {
				$this->dirty->$name = $asc->saveReferenced($this, $value);
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


	/**
	 * Provides type casting of this class attributes.
	 *
	 * @param  string $name    attribute name
	 * @param  mixed  $value   attribute value
	 * @return void
	 */
	private function typeCast($name, $value) {
		if ($value === NULL || $value instanceof ActiveRecord || $value instanceof ActiveCollection)
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
	 * Returns a hash of data types of this object associated table columns (column => type).
	 *
	 * @return array
	 */
	public static function getTypes() {
		return TableHelper::getColumnTypes(self::getClass());
	}


	/**
	 * Returns a hash of this object associated table columns values (column => value).
	 *
	 * @return array
	 */
	public function getValues() {
		return RecordHelper::getValues($this, self::getColumnNames());
	}


	/**
	 * Assigns values of this object associated table columns values.
	 *
	 * @param array $input  values in array(column => value)
	 * @return void
	 */
	public function setValues(array $input) {
		RecordHelper::setValues($this, $input);
	}


	/**
	 * Returns a hash of all columns default values (column => default value)
	 *
	 * @return array
	 */
	private static function getDefaults() {
		return TableHelper::getColumnDefaults(self::getClass());
	}


	/**
	 * Returns a hash of all changed attributes (attr => new value)
	 *
	 * @return array
	 */
	public function getChanges() {
		$dirty = array();
		foreach ($this->originals as $attr => $orig)
			if ($orig instanceof ActiveRecord || $orig instanceof ActiveCollection)
				if ($orig->isDirty())
					$dirty[$attr] = $orig;
		return new Storage(array_merge((array) $this->dirty, $dirty));
	}


	/**
	 * Returns a hash of all original attributes (attr => original value)
	 *
	 * @return array
	 */
	public function getOriginals() {
		return clone $this->values;
	}


	/**
	 * Returns value of specified attribute.
	 *
	 * @param string $attr
	 * @return array
	 */
	public function getChange($attr) {
		return $this->getChanges()->$attr;
	}


	/**
	 * Returns original value of specified attribute.
	 *
	 * @param string $attr
	 * @return array
	 */
	public function getOriginal($attr) {
		return $this->getOriginals()->$attr;
	}



	/********************* events *********************/



	/**
	 * Calls public method if exists.
	 * @param  string
	 * @param  array
	 * @return bool  does method exist?
	 */
	private function tryCall($method, array $params) {
		$rc = $this->getReflection();
		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);
			if ($rm->isPublic() && !$rm->isAbstract() && $rm->isStatic()) {
				$rm->invokeNamedArgs($this, $params);
				return TRUE;
			}
		}
		return FALSE;
	}



	/********************* validation *********************/



	/**
	 * Returns validator object associated to this class.
	 *
	 * @return Validator
	 */
	protected function getValidator() {
		if (static::$validator === NULL)
			static::$validator = new Validator;

		return static::$validator;
	}


	/**
	 * Provides this object attributes validation.
	 *
	 * @return void
	 */
	public function validate() {
		$this->tryCall('beforeValidation', array('sender' => $this));
		// $this->getValidator()->validate($this);
		$this->tryCall('afterValidation', array('sender' => $this));
	}


	/**
	 * Is all attributes of this object valid?
	 *
	 * @return bool
	 */
	public function isValid() {
		try {
			$this->validate();
			return TRUE;

		} catch (ValidationException $e) {
			return FALSE;
		}
	}



	/********************* executors *********************/



	/**
	 * Checks if this object has unsaved changes.
	 *
	 * @return bool
	 */
	public function isDirty($attr = NULL) {
		if ($attr == NULL)
			return (bool) count($this->getChanges()) || $this->isNewRecord();

		$attrs = is_array($attr) ? $attr : array($attr);
		$changes = array_keys((array) $this->getChanges());
		foreach ($attrs as $attr)
			if (in_array($attr, $changes))
				return TRUE;
		return FALSE;
	}


	/**
	 * Discards unsaved changes of this object to a similar state as was initialized (thus making all properties non dirty).
	 *
	 * @return void
	 */
	public function discard() {
		$this->updating();
		$this->dirty = $this->isNewRecord() ? $this->values : new Storage;
		foreach ($this->getChanges() as $unsaved)
			if ($unsaved->isDirty())
				$unsaved->discard();
	}


	/**
	 * Save the instance and loaded, dirty associations to the repository.
	 *
	 * @return ActiveRecord
	 */
	public function save() {
		if ($this->isValid() && $this->isDirty()) {
			try {
				$this->updating();
				$this->tryCall('beforeSave', array('sender' => $this));

				foreach ($this->getChanges() as $attr => $unsaved)
					if ($unsaved instanceof ActiveRecord || $unsaved instanceof ActiveCollection)
						$unsaved->save();

				if ($this->isDirty(self::getColumnNames())) {
					$mapper = self::getMapper();
					$mapper::save($this);
				}
				$this->values = new Storage($this->getValues());
				$this->dirty = new Storage;
				$this->state = self::STATE_EXISTING;
				$this->tryCall('afterSave', array('sender' => $this));

			} catch (DibiException $e) {
				throw new ActiveRecordException("Unable to save record.", 500, $e);
			}
		}
	}


	/**
	 * Deletes the record in the database and freezes this instance to reflect
	 * that no changes should be made (since they can‘t be persisted).
	 *
	 * @return bool  true if the record was destroyed
	 */
	public function destroy() {
		try {
			$this->updating();
			$this->tryCall('beforeDestroy', array('sender' => $this));

			$mapper = self::getMapper();
			$deleted = (bool) $mapper::delete($this);

			$this->dirty = new Storage;
			foreach ($this->values as & $v)
				$v = NULL;
			$this->state = self::STATE_DELETED;

			$this->tryCall('afterDestroy', array('sender' => $this));
			$this->freeze();
			return $deleted;

		} catch (DibiException $e) {
				throw new ActiveRecordException("Unable to destroy record.", 500, $e);
		}
	}


	/**
	 * Creates, saves and returns new record.
	 *
	 * @param array|ArrayObject $input
	 * @return ActiveRecord
	 */
	public static function create($input = array()) {
		$record = new static($input, self::STATE_NEW);
		$record->save();
		return $record;
	}



	/********************* counting *********************/



	/**
	 * Returns number of objects satisfactoring input conditions.
	 *
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
	 * Returns avarage value of specified column of objects stored in repository.
	 *
	 * @param string $column
	 * @return float|int
	 */
	public static function avarage($column) {
		return self::getConnection()->query('SELECT AVG(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Returns minimum value of specified column of objects stored in repository.
	 *
	 * @param string $column
	 * @return float|int
	 */
	public static function minimum($column) {
		return self::getConnection()->query('SELECT MIN(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Returns maximum value of specified column of objects stored in repository.
	 *
	 * @param string $column
	 * @return float|int
	 */
	public static function maximum($column) {
		return self::getConnection()->query('SELECT MAX(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}


	/**
	 * Returns sum of specified column of objects stored in repository.
	 *
	 * @param string $column
	 * @return float|int
	 */
	public static function sum($column) {
		return self::getConnection()->query('SELECT SUM(%n) FROM (%sql)', $column, (string) self::getDataSource())->fetchSingle();
	}



	/********************* finders *********************/



	/**
	 * Django-like alias to find().
	 *
	 * @return ActiveCollection
	 */
	public static function objects() {
		return self::findAll();
	}


	/**
	 * Static finder.
	 *
	 * @param array $where
	 * @param array $order
	 * @return ActiveRecord|ActiveCollection|NULL
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
	 *
	 * @param array $where
	 * @param array $order
	 * @param array $limit
	 * @param array $offset
	 * @return ActiveCollection|NULL
	 */
	public static function findAll($where = array(), $order = array(), $limit = NULL, $offset = NULL) {
		$mapper = self::getMapper();
		return $mapper::find(self::getClass(), array('where' => $where, 'order' => $order, 'limit' => $limit, 'offset' => $offset), IMapper::ALL);
	}


	/**
	 * Internal finder.
	 * 
	 * @param array $where
	 * @param string $scope
	 * @return ActiveCollection|ActiveRecord|NULL
	 */
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
	 * @return ActiveCollection|ActiveRecord|NULL
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
			if (self::hasAttribute($name)) {
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
			if (self::hasAttribute($name)) {
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
		return ObjectMixin::has($this, $name) ? TRUE : self::hasAttribute($name);
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