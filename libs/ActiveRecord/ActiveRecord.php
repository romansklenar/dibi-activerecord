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
	protected static $foreingMask;

	/** @var string  used connection name */
	protected static $connection = Mapper::DEFAULT_CONNECTION;

	/** @var array  detected primary key name and table name cache */
	private static $cache = array();

	/** @var Events */
	protected static $events;

	/** @var IMapper */
	protected static $mapper;

	/** @var IValidator */
	protected static $validator;


	/**
	 * ActiveRecord constructor.
	 * @param ArrayObject|array $input
	 * @param int $state  does data physically exists in database?
	 */
	public function __construct($input = array(), $state = NULL) {
		if ($this->getMapper() === NULL)
			$this->setMapper(new Mapper($this)); //new Mapper($this->getClass(), $this->getTableName(), $this->getPrimaryName(), $this->getConnectionName());

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
			if (!isset(self::$cache[$this->class]['table']))
				self::$cache[$this->class]['table'] = Inflector::tableize($this->class, TRUE);
			return self::$cache[$this->class]['table'];
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
			if (!isset(self::$cache[$this->class]['primary'])) {
				$primary = array();
				$info = $this->getPrimaryInfo();
				foreach ($info->getColumns() as $column)
					$primary[] = $column->getName();
				
				self::$cache[$this->class]['primary'] = count($primary) == 1 ? $primary[0] : $primary;
			}
			return self::$cache[$this->class]['primary'];
		}
	}

	protected function detectPrimaryName() {
		$primary = array();
		$pk = Mapper::getPrimaryInfo($this->getTableName(), $this->getConnectionName()); // intentionally from Mapper

		if (!$pk instanceof DibiIndexInfo)
			throw new InvalidStateException("Table '$this->tableName' has not defined primay key index" .
				" or unable to detect it. You cau try manually define it to $this->class::\$primary variable.");

		foreach ($pk->getColumns() as $column)
			$primary[] = $column->getName();

		return count($primary) == 1 ? $primary[0] : $primary;
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
	 * Gets current primary key(s) formated for use in array-style-condition.
	 * @return array
	 */
	public function getPrimaryCondition() {
		$condition = array();
		foreach	($this->getPrimaryInfo()->columns as $column)
			$condition[$column->name . '%' . $column->type] = $this->originalValues[$column->name]; // $this->getStorage()->original[$column->name];

		return $condition;
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
		return Mapper::getColumnDefaults($this->getTableName(), $this->getConnectionName());
	}


	/**
	 * Gets record's columns names
	 * @retrun array
	 */
	public function getColumnNames() {
		return Mapper::getColumnNames($this->getTableName(), $this->getConnectionName());
	}


	/**
	 * Gets table's reflection meta object
	 * @return DibiTableInfo
	 */
	public function getTableInfo() {
		return Mapper::getTableInfo($this->getTableName(), $this->getConnectionName());
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
		$primary = Mapper::getPrimaryInfo($this->getTableName(), $this->getConnectionName());
		
		if ($primary instanceof  DibiIndexInfo)
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
		return Mapper::getColumnTypes($this->getTableName(), $this->getConnectionName());
	}


	/**
	 * Gets record's assotiations.
	 * @return array
	 */
	public function getAssotiations($type = NULL) {
		$asc = Association::getAssotiations($this->getReflection(), $this->getClass());
		return $type === NULL ? $asc : (isset($asc[$type]) ? $asc[$type] : array());
	}


	/**
	 * Gets database connection
	 * @return DibiConnection
	 */
	public function getConnection() {
		return Mapper::getConnection($this->getConnectionName());
	}


	/**
	 * Gets record's mapper.
	 * @return Mapper
	 */
	public function getMapper() {
		return isset(self::$cache[$this->class]['mapper']) ? self::$cache[$this->class]['mapper'] : NULL;
	}


	/**
	 * Gets record's mapper.
	 * @param Mapper $mapper
	 */
	protected function setMapper(Mapper $mapper) {
		self::$cache[$this->class]['mapper'] = $mapper;
	}


	/**
	 * Gets record's events.
	 * @return Events
	 */
	protected function getEvents() {
		throw new NotImplementedException;
		
		if (static::$events === NULL)
			static::$events = new Events;
	
		return static::$events;
	}


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



	/********************* record executors *********************/



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
		$this->getMapper()->save($this);
		parent::save();
		return $this;
	}


	/**
	 * Destroy the instance, remove it from the repository.
	 * @return bool  true if Record was destroyed
	 */
	public function destroy() {
		$deleted = (bool) $this->getMapper()->delete($this);
		parent::destroy();
		return $deleted;
	}


	/**
	 * Counter.
	 * @param array $conditions
	 * @return int
	 */
	public static function count($conditions = array(), $limit = NULL, $offset = NULL) {
		$record = self::create();
		
		if (!is_array($conditions) && (is_numeric($conditions) || (is_string($conditions) && str_word_count($conditions) == 1)))
			$conditions = array(Mapper::formatConditions($record->getPrimaryInfo(), func_get_args())); // intentionally not getPrimaryInfo() from Mapper

		return $record->getMapper()->count($conditions, $limit, $offset);
	}


	/**
	 * Django-like alias to find().
	 * @param array $conditions
	 * @return ActiveRecordCollection
	 */
	public static function objects() {
		return self::find();
	}


	/**
	 * Finder.
	 * @param array $conditions
	 * @return ActiveRecordCollection|ActiveRecord
	 */
	public static function find($conditions = array(), $order = array(), $limit = NULL, $offset = NULL) {
		$record = self::create();

		if (!is_array($conditions) && (is_numeric($conditions) || (is_string($conditions) && str_word_count($conditions) == 1))) {
			$params = func_get_args();
			$conditions = Mapper::formatConditions($record->getPrimaryInfo(), $params); // intentionally not getPrimaryInfo() from Mapper

			if (count($params) == 1)
				return $record->getMapper()->find(array($conditions), array(), 1)->first(); // self::findOne(array($conditions));
			else
				return $record->getMapper()->find(array($conditions)); // self::find(array($conditions));
			
		} else {
			return $record->getMapper()->find($conditions, $order, $limit, $offset);
		}
	}


	/**
	 * Finder.
	 * @param array $conditions
	 * @return ActiveRecord
	 */
	public static function findOne($conditions = array(), $order = array()) {
		return self::find($conditions, $order, 1)->first();
	}

	
	/**
	 * Active Record factory
	 * @return ActiveRecord
	 */
	public static function create($data = array()) {
		return new static($data, Record::STATE_NEW);
	}


	/**
	 * Magic find.
	 * - $rec = Page::findOneByUrl('about-us');
	 * - $col = Page::findByCategoryIdAndVisibility(5, TRUE);
	 * - $col = User::findByNameAndLogin('John', 'john007');
	 * - $col = Product::findByCategory(3);
	 *
	 * @param string $name
	 * @param array  $args
	 * @return ActiveRecordCollection|ActiveRecord|NULL
	 */
	public static function __callStatic($name, $args) {
		if (strncmp($name, 'findBy', 6) === 0) { // record collection
			$method = 'find';
			$name = substr($name, 6);

		} elseif (strncmp($name, 'findOneBy', 9) === 0) { // single record
			$method = 'findOne';
			$name = substr($name, 9);

		} else {
			return parent::__callStatic($name, $args);
		}

		// ProductIdAndTitle -> array('productId', 'title')
		$parts = array_map('lcfirst', explode('And', $name));

		if (count($parts) !== count($args)) {
			throw new InvalidArgumentException("Magic find expects " . count($parts) . " parameters, but " . count($args) . " was given.");
		}

		$cond = array_combine($parts, $args);
		$mapper = self::create()->getMapper();

		return $method == 'findOne' ? $mapper->find($cond, array(), 1)->first() : $mapper->find($cond);
	}

}