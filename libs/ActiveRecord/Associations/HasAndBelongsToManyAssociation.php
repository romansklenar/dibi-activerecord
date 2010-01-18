<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class HasAndBelongsToManyAssociation extends Association {

	/** @var string */
	public $intersectEntity;


	/**
	 * Association constructor.
	 *
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 * @param string $intersectEntity
	 */
	public function __construct($local, $referenced, $intersectEntity = NULL) {
		parent::__construct(self::HAS_AND_BELONGS_TO_MANY, $local, $referenced);
		$this->intersectEntity = $intersectEntity;
	}


	/**
	 * Retreives referenced object(s).
	 * 
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$class = $this->referenced;
		$entity = $this->getIntersectEntity();
		$sub = $record->connection->dataSource($entity)->select($class::getForeignKey())->where('%and', RecordHelper::formatForeignKey($record));
		$ds = $class::getDataSource()->where('%n IN (%sql)', $class::getPrimaryKey(), (string) $sub);
		return new ActiveRecordCollection($ds, $class);
	}


	/**
	 * Intersect entity name lazy getter.
	 * 
	 * @return string  intersect entity table name
	 */
	public function getIntersectEntity() {
		$class = $this->local;
		$database = $class::getConnection()->getDatabaseInfo();
		if ($this->intersectEntity == NULL) {
			if ($database->hasTable($name = Inflector::intersectEntity($this->local, $this->referenced)))
				return $this->intersectEntity = $name;
			else if ($database->hasTable($alternate = Inflector::intersectEntity($this->referenced, $this->local)))
				return $this->intersectEntity = $alternate;
			else
				throw new InvalidStateException("Intersect entity '$name' or '$alternate' of many-to-many relation not found in a database $database->name");
		}
		return $this->intersectEntity;
	}


	/**
	 * Links referenced object to record.
	 * @param  ActiveRecord $local
	 * @param  ActiveRecord|ActiveRecordCollection|NULL $referenced
	 */
	public function saveReferenced(ActiveRecord $local, $referenced) {
		$class = $this->referenced;
		$entity = $this->getIntersectEntity();
		$connection = $local->connection;
		try {
			$connection->update($entity, array($local->foreignKey => NULL))
				->where(array(array('%n IN %l', $local->foreignKey, array($local->{$local->primaryKey}))))
				->execute();

		} catch (DibiException $e) {
			$connection->delete($entity)
				->where(array(array('%n IN %l', $local->foreignKey, array($local->{$local->primaryKey}))))
				->execute();
		}

		$class = $this->referenced;
		$connection->update($entity, array($local->foreignKey => $local->{$local->primaryKey}))
			->where(array(array('%n IN %l', $class::getForeignKey(), $referenced->{$class::getPrimaryKey()})))
			->execute();

		$class = $this->referenced;
		return $class::findAll(array(array('%n IN %l', $class::getPrimaryKey(), $referenced->{$class::getPrimaryKey()})));
	}
}