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
		$referenced = new $this->referenced;
		$entity = $this->getIntersectEntity($record->connection->getDatabaseInfo());
		$sub = $record->connection->dataSource($entity)->select($referenced->foreignKey)->where('%and', RecordHelper::formatForeignKey($record));
		$ds = $referenced->dataSource->where('%n IN (%sql)', $referenced->primaryKey, (string) $sub);
		return new ActiveRecordCollection($ds, $this->referenced);
	}


	/**
	 * Intersect entity name lazy getter.
	 * 
	 * @param DibiDatabaseInfo $database
	 * @return string  intersect entity table name
	 */
	public function getIntersectEntity(DibiDatabaseInfo $database) {
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
	 * @param  ActiveRecord $record
	 * @param  ActiveRecord|ActiveRecordCollection|NULL $new
	 */
	public function linkWithReferenced(ActiveRecord $record, $new) {
		return $new;
	}
}