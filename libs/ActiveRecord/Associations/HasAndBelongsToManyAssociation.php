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
	 * @param  ActiveRecord $local
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $local) {
		$referenced = new $this->referenced;
		$entity = $this->getIntersectEntity($local->tableName, $referenced->tableName, $local->getConnection()->getDatabaseInfo());
		$sub = $local->getConnection()->dataSource($entity)->select($referenced->foreignMask)->where('%and', $local->foreignCondition);
		$ds = $referenced->getDataSource()->where('%n IN (%sql)', $referenced->primaryName, (string) $sub);
		return new ActiveRecordCollection($ds, $referenced->getMapper());
	}


	/**
	 * Intersect entity name lazy getter.
	 * 
	 * @param string $local
	 * @param string $referenced
	 * @param DibiDatabaseInfo $database
	 * @return string  intersect entity name
	 */
	public function getIntersectEntity($local, $referenced, DibiDatabaseInfo $database) {
		if ($this->intersectEntity == NULL) {
			if ($database->hasTable($name = Inflector::intersectEntity($local, $referenced)))
				return $this->intersectEntity = $name;
			else if ($database->hasTable($alternate = Inflector::intersectEntity($referenced, $local)))
				return $this->intersectEntity = $alternate;
			else
				throw new InvalidStateException("Intersect entity '$name' or '$alternate' of many-to-many relation not found in a database $database->name");
		}
		return $this->intersectEntity;
	}
}