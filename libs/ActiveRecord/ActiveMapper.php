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

	const COLLECTION_CLASS = 'ActiveCollection';


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
		if (isset($options['where']) && !empty($options['where'])) {
			$cond = $options['where'];
			if (is_string($cond))
				$ds->where($cond);
			else
				$ds->where('%and', $cond);
		}

		if (isset($options['order']) && !empty($options['order']))
			$ds->orderBy($options['order']);

		if (isset($options['limit']) && !empty($options['limit']))
			$ds->applyLimit($options['limit'], $options['offset']);
	}



	/********************* IMapper interface *********************/



	/**
	 * @param ActiveRecord|string $class
	 * @param array $options
	 * @param string $scope
	 * @return ActiveCollection|ActiveRecord
	 */
	public static function find($class, $options = array(), $scope = IMapper::ALL) {
		static $scopes = array(IMapper::ALL, IMapper::FIRST, IMapper::LAST);
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

		$ds = $record->dataSource;
		self::applyOptions($ds, self::sanatizeOptions($options));

		if ($scope == IMapper::FIRST || $scope == IMapper::LAST) {
			if ($scope == IMapper::LAST)
				$ds = $ds->toDataSource()->applyLimit(1, $ds->count()-1);
			
			$res = $ds->getResult();
			$res->setRowClass($class);
			$res->detectTypes(); // intentionally not $res->setTypes($record->types) - in selection to database must be used detection from DibiColumnInfo::detectType to detect types 100% correctly
			return $res->fetch();

		} else {
			$collection = self::COLLECTION_CLASS;
			return new $collection($ds, $class);
		}
	}

	public static function save(Record $record) {
		// TODO: transaction
		if ($record->isDirty()) {
			if ($record->isNewRecord()) {
				$value = self::insert($record);
				$class = $record->class;

				$primary = $record->primaryInfo;
				if (TableHelper::isPrimarySingle($primary)) {
					if (TableHelper::isPrimaryAutoIncrement($primary))
						$record->{$record->primaryKey} = $value;
					else if ($value = $record->connection->getDriver()->getInsertId(NULL))
						$record->{$record->primaryKey} = $value;
					else
						throw new InvalidStateException("Unable to refresh record's primary key value after INSERT.");

					$column = $record->primaryInfo->columns[0];
					$cond = array('%n = %' . $column->type, $column->name, $record->{$column->name});
					$record = self::find($record, array('where' => array($cond)))->first();

				} else {
					$cond = array();
					foreach ($primary->columns as $column)
						$cond[] = array('%n = %' . $column->type, $column->name, $record->{$column->name});
					$record = self::find($record, array('where' => $cond))->first();
				}

			} else {
				self::update($record);
			}
		}
		return $record;
	}

	public static function update(Record $record) {
		if ($record->isNewRecord())
			throw new LogicException("Cannot update non-existing record.");

		$record->connection
			->update($record->tableName, RecordHelper::formatChanges($record))
			->where('%and', RecordHelper::formatPrimaryKey($record))
			->execute();

		return $record->connection->affectedRows();
	}

	public static function insert(Record $record) {
		if ($record->isExistingRecord())
			throw new LogicException("Cannot insert existing record.");
		
		return $record->connection
			->insert($record->tableName, RecordHelper::formatChanges($record))
			->execute(dibi::IDENTIFIER);
	}

	public static function delete(Record $record) {
		$record->connection->delete($record->tableName)
			->where('%and', RecordHelper::formatPrimaryKey($record))->execute();
		return $record->connection->affectedRows();
	}

}