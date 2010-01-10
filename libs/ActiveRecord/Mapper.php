<?php

/**
 * Base mapper class by pattern Table Data Gateway.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class Mapper extends Object implements IMapper {

	/** @var ActiveRecord */
	private $record;

	/** @var string */
	private $collectionClass = 'ActiveRecordCollection';

	const DEFAULT_CONNECTION = '#AR';


	/**
	 * Mapper's constructor.
	 * @param ActiveRecord $record
	 */
	public function __construct(ActiveRecord $record) {
		$this->record = clone $record;
	}



	/********************* Public getters *********************/



	/**
	 * Gets record row class.
	 * @return string
	 */
	public function getRowClass() {
		return $this->record->getClass();
	}


	/**
	 * Gets record column types in array(column => type)
	 * @return array
	 */
	public function getTypes() {
		return $this->record->getTypes();
	}


	/**
	 * Creates a new DibiConnection object and connects it to specified database.
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
	 * @param  string $name  connection name
	 * @return bool
	 */
	public static function isConnected($name = self::DEFAULT_CONNECTION) {
		return self::getConnection($name)->isConnected();
	}



	/**
	 * Retrieve active connection.
	 * @param  string $name   connection registy name
	 * @return DibiConnection
	 * @throws DibiException
	 */
	public static function getConnection($name = self::DEFAULT_CONNECTION) {
		return dibi::getConnection($name);
	}



	/********************* IMapper interface *********************/



	/**
	 * Find occurrences matching conditions.
	 * @return ActiveRecordCollection|ActiveRecord
	 */
	public function find($conditions = array(), $order = array(), $limit = NULL, $offset = NULL) {
		if (!is_array($conditions) && !is_string($conditions)) {
			$params = func_get_args();
			$conditions = RecordHelper::formatConditions($this->record->getPrimaryInfo(), $params);
			$result = $this->find(array($conditions));
			return (count($params) == 1) ? $result->first() : $result;
		}

		if (!empty($order) && is_string($order)) {
			$tmp = $order;
			$order = array();
			foreach(explode(',', $tmp) as $o) {
				$o = explode(' ', trim($o));
				$order[trim($o[0], '[]')] = trim($o[1]);
			}
		} else if ($order == NULL) {
			$order = array();
		}

		$ds = $this->record->getDataSource()->orderBy($order)->applyLimit($limit, $offset);

		if (!empty($conditions)) {
			if (is_string($conditions))
				$ds->where($conditions);
			else
				$ds->where('%and', $conditions);
		}

		$class = $this->collectionClass;
		return new $class($ds, $this);
	}


	/**
	 * Counter.
	 * @return int
	 */
	public function count($conditions = array(), $limit = NULL, $offset = NULL) {
		return count($this->find($conditions, NULL, $limit, $offset));
	}


	/**
	 * Saves the instance and loaded, dirty associations to the database.
	 * @param ActiveRecord $record
	 * @return void
	 */
	public function save(ActiveRecord $record) {
		// TODO: do transakce
		if ($record->isDirty()) {
			if ($record->isRecordNew()) {
				$value = $this->insert($record);

				$pk = $record->getPrimaryInfo();
				if (count($pk->columns) == 1) {
					if ($pk->columns[0]->isAutoincrement())
						$record->{$record->getPrimaryName()} = $value;
					else if ($value = $record->getMapper()->getConnection()->getDriver()->getInsertId(NULL))
						$record = $this->find($value);
					else
						throw new InvalidStateException("Unable to refresh record's primary key value after INSERT.");

				} else {
					$cond = array();
					foreach ($pk->columns as $column)
						$cond[] = array("%n = {$column->type}", $column->name, $record->$column);
					$record = $this->find($cond, array(), 1);
				}

			} else {
				$record->getMapper()->update($record);
			}
		}
		return $record;
	}


	/**
	 * Updates database row(s).
	 * @param ActiveRecord $record
	 * @return int  number of updated rows
	 */
	public function update(ActiveRecord $record) {
		if ($record->isRecordNew())
			throw new InvalidStateException("Cannot update non-existing record.");
		
		$record->getConnection()
			->update($record->getTableName(), $record->getModifiedValues())
			->where('%and', $record->getPrimaryCondition())
			->execute();
		
		return $record->getConnection()->affectedRows();
	}


	/**
	 * Inserts data into table.
	 * @param ActiveRecord $record
	 * @return int  new primary key
	 */
	public function insert(ActiveRecord $record) {
		if ($record->isRecordExisting())
			throw new InvalidStateException("Cannot insert existing record.");
		
		$values = $record->getModifiedValues();
		if ($record->getPrimaryInfo()->columns[0]->isAutoincrement()) {
			unset($values[$record->getPrimaryInfo()->columns[0]->getName()]);
		}
		return $record->getConnection()->insert($record->getTableName(), $values)->execute(dibi::IDENTIFIER);
	}


	/**
	 * Deletes row(s) matching primary key.
	 * @param ActiveRecord $record
	 * @return int  number of deleted rows
	 */
	public function delete(ActiveRecord $record) {
		$record->getConnection()->delete($record->getTableName())
			->where('%and', $record->getPrimaryCondition())->execute();
		return $record->getConnection()->affectedRows();
	}



	/***** Additional mapper's API *****/



	/**
	 * Magic find.
	 * - $row = $mapper->findOneByUrl('about-us');
	 * - $arr = $mapper->findByCategoryIdAndVisibility(5, TRUE);
	 * - $arr = $mapper->findByNameAndLogin('John', 'john007');
	 * - $flu = $mapper->findByCategory(3);
	 *
	 * @param string $name
	 * @param array  $args
	 * @return ActiveRecordCollection|ActiveRecord|NULL
	 */
	public function __call($name, $args) {
		if (strncmp($name, 'findBy', 6) === 0) { // row collection
			$method = 'find';
			$name = substr($name, 6);

		} elseif (strncmp($name, 'findOneBy', 9) === 0) { // single row
			$method = 'findOne';
			$name = substr($name, 9);

		} else {
			return parent::__call($name, $args);
		}

		// ProductIdAndTitle -> array('productId', 'title')
		$parts = array_map('lcfirst', explode('And', $name));

		if (count($parts) !== count($args))
			throw new InvalidArgumentException("Magic find expects " . count($parts) . " parameters, but " . count($args) . " was given.");

		$cond = array_combine($parts, $args);
		return $method == 'findOne' ? $this->find($cond, array(), 1)->first() : $this->find($cond);
	}

}