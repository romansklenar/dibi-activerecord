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
	protected static $foreing; # i.e. '%table%Id', '%table%_id', '%table%_%primary%', '%primary%'

	/** @var string  used connection name */
	protected static $connection = ActiveMapper::DEFAULT_CONNECTION;

	/** @var array  detected primary key name and table name cache */
	private static $register = array();

	/** @var Events */
	protected static $events;

	/** @var string  used mapper class */
	protected static $mapper = 'ActiveMapper';

	/** @var IValidator */
	protected static $validator;


	/**
	 * ActiveRecord constructor.
	 * @param ArrayObject|array $input
	 * @param int $state  does data physically exists in database?
	 */
	public function __construct($input = array(), $state = NULL) {
		if ($state === NULL)
			$state = $this->detectState((array) $input);

		parent::__construct($input, $state);
		unset($this->columns, $this->defaults, $this->types);
	}


	public function  __destruct() {
		// TODO: rollback všech nedokončených transakcí
	}


	/**
	 * Detects record's state.
	 * @param array $input
	 * @return int
	 */
	protected function detectState(array $input) {
		$state = parent::detectState($input);

		if ($state == self::STATE_EXISTING) {
			$primary = is_array($this->getPrimaryName()) ? $this->getPrimaryName() : array($this->getPrimaryName());
			foreach ($primary as $key) {
				if (isset($input[$key])) {
					if ($input[$key] === NULL)
						return self::STATE_NEW;
				} else {
					return self::STATE_NEW;
				}
			}
		}

		return $state;
	}


	/**
	 * Gets record's connection name
	 * @return string
	 */
	protected function getConnectionName() {
		return static::$connection;
	}


	/**
	 * Gets record's table name
	 * @return string
	 */
	public function getTableName() {
		if (isset(static::$table) && static::$table !== NULL) {
			return static::$table;

		} else {
			if (!isset(self::$register[$this->class]['table']))
				self::$register[$this->class]['table'] = Inflector::tableize($this->class);
			return self::$register[$this->class]['table'];
		}
	}


	/**
	 * Gets record's foreign key mask
	 * @return string
	 */
	public function getForeign() {
		if (isset(static::$foreing) && static::$foreing !== NULL) {
			return str_replace(
				array('%table%', '%primary%'),
				array($this->getTableName(), $this->getPrimaryName()),
				static::$foreing
			);

		} else {
			if (!isset(self::$register[$this->class]['foreign']))
				self::$register[$this->class]['foreign'] = Inflector::foreignKey($this->class);
			return self::$register[$this->class]['foreign'];
		}
	}


	/**
	 * Gets record's primary key column(s) name
	 * @return string|array
	 */
	public function getPrimaryName() {
		if (isset(static::$primary) && static::$primary !== NULL) {
			return static::$primary;

		} else {
			if (!isset(self::$register[$this->class]['primary'])) {
				$primary = array();
				$info = $this->getPrimaryInfo();
				foreach ($info->getColumns() as $column)
					$primary[] = $column->getName();

				self::$register[$this->class]['primary'] = count($primary) == 1 ? $primary[0] : $primary;
			}
			return self::$register[$this->class]['primary'];
		}
	}

	protected function detectPrimaryName() {
		$primary = array();
		$pk = RecordHelper::getPrimaryInfo($this->getTableName(), $this->getConnectionName()); // intentionally from helper

		if (!$pk instanceof DibiIndexInfo)
			throw new InvalidStateException("Table '$this->tableName' has not defined primay key index" .
				" or unable to detect it. You cau try manually define it to $this->class::\$primary variable.");

		foreach ($pk->getColumns() as $column)
			$primary[] = $column->getName();

		return count($primary) == 1 ? $primary[0] : $primary;
	}


	/**
	 * DibiDataSource finder factory.
	 * @return DibiDataSource
	 */
	public function getDataSource() {
		return $this->getConnection()->dataSource($this->getTableName());
	}


	/**
	 * Gets record's primary key column(s) value(s)
	 * @return string|array
	 */
	public function getPrimaryValue() {
		if (!is_array($this->getPrimaryName()))
			return $this->originalValues[$this->getPrimaryName()];

		$values = array();
		foreach	($this->getPrimaryName() as $field)
			$values[$field] = $this->originalValues[$field];

		return $values;
	}


	/**
	 * Gets current primary key(s) type(s) and value(s) formated for use in array-style-condition.
	 * @return array
	 */
	public function getPrimaryCondition() {
		$cond = array();
		foreach	($this->getPrimaryInfo()->columns as $column)
			$cond[$column->name . '%' . $column->type] = $this->originalValues[$column->name]; // $this->getStorage()->original[$column->name];

		return $cond;
	}


	/**
	 * Gets current foreign key type and value formated for use in array-style-condition.
	 * @return array
	 */
	public function getForeignCondition() {
		if (is_array($this->getPrimaryName()))
			throw new InvalidStateException("You cannot use this format of conditions when table has primary key composed from more then one column.");

		$column = $this->getPrimaryInfo()->columns[0];
		$cond = array();
		$cond[$this->getForeign() . '%' . $column->type] = $this->getPrimaryValue(); // $this->getStorage()->original[$column->name];
		return $cond;
	}


	/**
	 * Gets record's modified values in array(column%type => value)
	 * @return array
	 */
	public function getModifiedValues() {
		$modified = parent::getModifiedValues();

		if ($this->isRecordNew()) {
			foreach ($this->getPrimaryInfo()->getColumns() as $column) {
				if ($column->isAutoIncrement() && array_key_exists($column->getName(), $modified)) {
					unset($modified[$column->getName()]);
				}
			}
		}

		$types = $this->getTypes();
		$result = array();

		foreach ($modified as $column => $value)
			$result[$column . '%' . $types[$column]] = $value;

		return $result;
	}


	/**
	 * Gets record's original values in array(column => value)
	 * @return array
	 */
	public function getOriginalValues() {
		return parent::getOriginalValues();
	}


	/**
	 * Gets record's columns default values in array(column => defaultValue)
	 * @retrun array
	 */
	protected function getDefaultValues() {
		return RecordHelper::getColumnDefaults($this);
	}


	/**
	 * Gets record's columns names
	 * @retrun array
	 */
	public function getColumnNames() {
		return RecordHelper::getColumnNames($this);
	}


	/**
	 * Gets table's reflection meta object
	 * @return DibiTableInfo
	 */
	public function getTableInfo() {
		return RecordHelper::getTableInfo($this);
	}


	/**
	 * Gets table's primary key index reflection meta object
	 * @return DibiIndexInfo
	 */
	public function getPrimaryInfo() {
		// hook for database which do not support index reflection (in specific DibiDriver)
		if (isset(static::$primary) && static::$primary !== NULL) {
			$primary = $this->getPrimaryName();
			$info = array(
				'name' => $this->getTableName() . '_primary',
				'columns' => is_array($primary) ? $primary : array($primary),
				'unique' => FALSE,
				'primary' => TRUE,
			);

			foreach ($info['columns'] as $key => $name) {
				$info['columns'][$key] = $this->getTableInfo()->getColumn($name);
				if ($info['columns'][$key]->isAutoIncrement())
					$info['unique'] = TRUE;
			}
			return new DibiIndexInfo($info);
		}

		// detect
		$primary = RecordHelper::getPrimaryInfo($this);

		if ($primary instanceof DibiIndexInfo)
			return $primary;
		else
			throw new InvalidStateException("Table '$this->tableName' has not defined primay key index" .
				" or dibi was unable to detect it. You can try manually define primary key column(s) to $this->class::\$primary variable.");
	}


	/**
	 * Gets table's column types in array(column => type)
	 * @return array
	 */
	public function getTypes() {
		return RecordHelper::getColumnTypes($this);
	}


	/**
	 * Gets record's assotiations.
	 * @return array
	 */
	public function getAssotiations($type = NULL) {
		$asc = RecordHelper::getAssotiations($this);
		return $type === NULL ? $asc : (isset($asc[$type]) ? $asc[$type] : array());
	}


	/**
	 * Gets database connection
	 * @return DibiConnection
	 */
	public function getConnection() {
		return ActiveMapper::getConnection($this->getConnectionName());
	}


	/**
	 * Gets associated mapper class name.
	 * @return string
	 */
	protected static function mapper() {
		return static::$mapper;
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
		// TODO: zohlednit asociace
		return parent::isDirty();
	}


	/**
	 * Checks if the Record has no changes to save.
	 * @return bool
	 */
	public function isClean() {
		// TODO: zohlednit asociace
		return parent::isClean();
	}


	/**
	 * Makes all properties Record's non dirty.
	 * @return void
	 */
	protected function clean() {
		// TODO: zohlednit asociace
		parent::clean();
	}


	/**
	 * Save the instance and loaded, dirty associations to the repository.
	 * @return ActiveRecord
	 */
	public function save() {
		$mapper = self::mapper();
		$mapper::save($this);
		parent::save();
		return $this;
	}


	/**
	 * Destroy the instance, remove it from the repository.
	 * @return bool  true if Record was destroyed
	 */
	public function destroy() {
		$mapper = self::mapper();
		$deleted = (bool) $mapper::delete($this);
		parent::destroy();
		return $deleted;
	}


	/**
	 * Active Record factory
	 * @return ActiveRecord
	 */
	public static function create($data = array()) {
		return new static($data, Record::STATE_NEW);
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
	 * Counter.
	 * @param array $where
	 * @param array $limit
	 * @param array $offset
	 * @return int
	 */
	public static function count($where = array(), $limit = NULL, $offset = NULL) {
		if (!is_array($where) && (is_numeric($where) || (is_string($where) && str_word_count($where) == 1)))
			$where = array(RecordHelper::formatConditions(self::create()->getPrimaryInfo(), func_get_args())); // intentionally not getPrimaryInfo() from helper

		$class  = get_called_class();
		$mapper = self::mapper();
		return $mapper::find($class, array('where' => $where, 'limit' => $limit, 'limit' => $offset), 'all')->count();
	}


	/**
	 * Finder.
	 * @param array $where
	 * @param array $order
	 * @param array $limit
	 * @param array $offset
	 * @return ActiveRecordCollection|NULL
	 */
	public static function findAll($where = array(), $order = array(), $limit = NULL, $offset = NULL) {
		$class  = get_called_class();
		$mapper = self::mapper();
		return $mapper::find($class, array('where' => $where, 'order' => $order, 'limit' => $limit, 'limit' => $offset), 'all');
	}


	/**
	 * Static finder.
	 * @param array $where
	 * @param array $order
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public static function find($where = array(), $order = array()) {
		$class  = get_called_class();
		$mapper = self::mapper();

		if (!is_array($where) && (is_numeric($where) || (is_string($where) && str_word_count($where) == 1))) {
			$params = func_get_args();
			$where = RecordHelper::formatConditions(self::create()->getPrimaryInfo(), $params); // intentionally not getPrimaryInfo() from helper

			if (count($params) == 1) {
				Debug::dump($where);
				return $mapper::find($class, array('where' => array($where)), 'first');
			} else
				return $mapper::find($class, array('where' => array($where)), 'all');

		} else {
			return $mapper::find($class, array('where' => $where, 'order' => $order), 'first');
		}
	}


	/**
	 * Static Magic find.
	 * - $rec = Page::findAllByUrl('about-us');
	 * - $col = Page::findByCategoryIdAndVisibility(5, TRUE);
	 * - $col = User::findByNameAndLogin('John', 'john007');
	 * - $col = Product::findByCategory(3);
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

		$options = array('where' => array_combine($parts, $args));
		$class  = get_called_class();
		$mapper = self::mapper();
		return $mapper::find($class, $options, $method == 'find' ? 'first' : 'all');
	}



	/**
	 * Magic find.
	 * - $rec = $page->findAllByUrl('about-us');
	 * - $col = $page->findByCategoryIdAndVisibility(5, TRUE);
	 * - $col = $page->findByNameAndLogin('John', 'john007');
	 * - $col = $product->findByCategory(3);
	 *
	 * @param string $name
	 * @param array  $args
	 * @return ActiveRecordCollection|ActiveRecord|NULL
	 */
	public function __call($name, $args) {
		if (strncmp($name, 'findBy', 6) === 0) { // single row
			$method = 'find';
			$name = substr($name, 6);

		} elseif (strncmp($name, 'findAllBy', 9) === 0) { // row collection
			$method = 'findAll';
			$name = substr($name, 9);

		} else {
			return parent::__call($name, $args);
		}

		// ProductIdAndTitle -> array('productId', 'title')
		$parts = array_map('lcfirst', explode('And', $name));

		if (count($parts) !== count($args))
			throw new InvalidArgumentException("Magic find expects " . count($parts) . " parameters, but " . count($args) . " was given.");

		$options = array('where' => array_combine($parts, $args));
		$class  = get_called_class();
		$mapper = self::mapper();
		return $mapper::find($class, $options, $method == 'find' ? 'first' : 'all');
	}



	/********************* assotiation handling *********************/



	/**
	 * Has record assotiation to another record?
	 * @param string $name  researched records's class name
	 * @return bool|Association
	 */
	protected function hasAssotiation($name) {
		if (Inflector::isSingular($name)) {
			$assotiations = array_merge($this->getAssotiations(Association::BELONGS_TO), $this->getAssotiations(Association::HAS_ONE));
			$name = Inflector::pluralize($name);
		} else {
			$assotiations = array_merge($this->getAssotiations(Association::HAS_MANY), $this->getAssotiations(Association::HAS_AND_BELONGS_TO_MANY));
		}

		foreach ($assotiations as $assotiation)
			if ($assotiation->isInRelation(Inflector::classify($name)))
				return $assotiation;

		return FALSE;
	}



	/********************* attributes handling *********************/



	protected function getAttributes() {
		return $this->getStorage();
	}

	protected function hasAttribute($name) {
		return isset($this->getAttributes()->$name);
	}

	protected function getAttribute($name) {
		$value = $this->getAttributes()->$name;
		return $this->cast($name, $value);
	}

	protected function setAttribute($name, $value) {
		$value = $this->cast($name, $value);
		$this->getAttributes()->$name = $value;
	}



	/********************* magic getters & setters *********************/



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name) {
		try {
			$value = ObjectMixin::get($this, $name);
			return $value;

		} catch(MemberAccessException $e) {
			if ($assotiation = $this->hasAssotiation($name)) {
				$value = $assotiation->retreiveReferenced($this);
				return $value;

			} else if ($this->hasAttribute($name)) {
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
	public function __set($name, $value) {
		$this->updating();

		try {
			ObjectMixin::set($this, $name, $value);

		} catch(MemberAccessException $e) {
			if ($assotiation = $this->hasAssotiation($name)) {
				// TODO: implement

			} else if ($this->hasAttribute($name)) {
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
	public function __isset($name) {
		return ObjectMixin::has($this, $name) ? TRUE : ($this->hasAssotiation($name) || $this->hasAttribute($name) ? TRUE : FALSE);
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





	/**
	 * Converts value to date-time format.
	 * @param  string key
	 * @param  string format (TRUE means DateTime object)
	 * @return mixed
	 */
	public function asDate($key, $format = NULL) {
		$time = $this[$key];
		if ((int) $time === 0) { // '', NULL, FALSE, '0000-00-00', ...
			return NULL;

		} elseif ($format === NULL) { // return timestamp (default)
			return is_numeric($time) ? (int) $time : strtotime($time);

		} elseif ($format === TRUE) { // return DateTime object
			return new DateTime(is_numeric($time) ? date('Y-m-d H:i:s', $time) : $time);

		} elseif (is_numeric($time)) { // single timestamp
			return date($format, $time);

		} else {
			$time = new DateTime($time);
			return $time->format($format);
		}
	}



	/**
	 * Converts value to boolean.
	 * @param  string key
	 * @return mixed
	 */
	public function asBool($key) {
		$value = $this[$key];
		if ($value === NULL || $value === FALSE) {
			return $value;

		} else {
			return ((bool) $value) && $value !== 'f' && $value !== 'F';
		}
	}
}