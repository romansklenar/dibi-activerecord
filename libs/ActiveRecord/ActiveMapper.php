<?php

/**
 * ActiveRecord mapper class by pattern Table Data Gateway.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class ActiveMapper extends Mapper {

	/** @var string */
	static $collectionClass = 'ActiveRecordCollection';


	private static function sanatizeOptions(& $options = array()) {
		$options['limit'] = isset($options['limit']) ? (int) $options['limit'] : NULL;
		$options['offset'] = isset($options['offset']) ? (int) $options['offset'] : NULL;


		if (isset($options['order'])) {
			$order = $options['order'];
			
			if (!empty($order) && is_string($order)) {
				$order = array();
				foreach(explode(',', $options['order']) as $o) {
					$o = explode(' ', trim($o));
					$order[trim($o[0], '[]')] = trim($o[1]);
				}
			}
			$options['order'] = $order;
		}

		return $options;
	}

	private static function applyOptions(DibiDataSource $ds, array $options) {
		if (isset($options['where'])) {
			$cond = $options['where'];
			if (is_string($cond))
				$ds->where($cond);
			else
				$ds->where('%and', $cond);
		}

		if (isset($options['order']))
			$ds->orderBy($options['order']);

		if (isset($options['limit']))
			$ds->applyLimit($options['limit'], $options['offset']);
	}



	/********************* IMapper interface *********************/



	/**
	 * @param ActiveRecord|string $class
	 * @param array $options
	 * @param string $scope
	 * @return ActiveRecordCollection|ActiveRecord
	 */
	public function find($class, $options = array(), $scope = 'all') {
		static $scopes = array('all', 'first', 'last');
		if (!in_array($scope, $scopes))
			throw new InvalidArgumentException("Invalid scope given, one of values " . implode(', ', $scopes) . " expected, '$scope' given.");

		if (is_string($class) && class_exists($class)) {
			$record = new $class;
		} else if ($class instanceof ActiveRecord) {
			$record = $class;
			$class = get_class($record);
		} else {
			$type = is_object($class) ? get_class($class) : gettype($class);
			throw new InvalidArgumentException("Invalid argument given, class name or 'ActiveRecord' instance expected, '$type' given.");
		}

		$ds = $record->getDataSource();
		self::applyOptions($ds, self::sanatizeOptions($options));

		if ($scope == 'first' || $scope == 'last') {
			// optimalization
			if ($scope == 'last')
				$ds = $ds->toDataSource()->applyLimit(1, $ds->count()-1);
			
			$res = $ds->getResult();
			$res->setRowClass($class);
			$res->detectTypes(); // intentionally not $res->setTypes($record->types) - in selection to database must be used detection from DibiColumnInfo::detectType to detect types 100% correctly
			return $res->fetch();

		} else {
			$collection = self::$collectionClass;
			return new $collection($ds, $class); // $collection = new $class($ds, $record);
			// return $scope == 'all' ? $collection : ($scope == 'first' ? $collection->first() : $collection->last());
		}
	}

	public function save(Record $record) {
		// TODO: do transakce
		if ($record->isDirty()) {
			if ($record->isRecordNew()) {
				$value = self::insert($record);

				$pk = $record->getPrimaryInfo();
				if (count($pk->columns) == 1) {
					if ($pk->columns[0]->isAutoincrement())
						$record->{$record->getPrimaryName()} = $value;
					else if ($value = $record->getConnection()->getDriver()->getInsertId(NULL))
						$record = self::find($record, $value);
					else
						throw new InvalidStateException("Unable to refresh record's primary key value after INSERT.");

				} else {
					$cond = array();
					foreach ($pk->columns as $column)
						$cond[] = array("%n = {$column->type}", $column->name, $record->$column);
					$record = self::find($record, array('where' => $cond))->first();
				}

			} else {
				self::update($record);
			}
		}
		return $record;
	}

	public function update(Record $record) {
		if ($record->isRecordNew())
			throw new LogicException("Cannot update non-existing record.");

		$record->getConnection()
			->update($record->getTableName(), $record->getModifiedValues())
			->where('%and', $record->getPrimaryCondition())
			->execute();

		return $record->getConnection()->affectedRows();
	}

	public function insert(Record $record) {
		if ($record->isRecordExisting())
			throw new LogicException("Cannot insert existing record.");

		$values = $record->getModifiedValues();
		if ($record->getPrimaryInfo()->columns[0]->isAutoincrement())
			unset($values[$record->getPrimaryInfo()->columns[0]->getName()]);
		
		return $record->getConnection()->insert($record->getTableName(), $values)->execute(dibi::IDENTIFIER);
	}

	public function delete(Record $record) {
		$record->getConnection()->delete($record->getTableName())
			->where('%and', $record->getPrimaryCondition())->execute();
		return $record->getConnection()->affectedRows();
	}

}