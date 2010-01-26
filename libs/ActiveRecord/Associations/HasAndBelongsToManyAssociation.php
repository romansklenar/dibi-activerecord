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
	 * @return ActiveRecord|ActiveCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$class = $this->referenced;
		$entity = $this->getIntersectEntity();
		$sub = $record->connection->dataSource($entity)->select(callback("$class::getForeignKey")->invoke()/*$class::getForeignKey()*/)->where('%and', RecordHelper::formatForeignKey($record));
		$ds = callback("$class::getDataSource")->invoke()->where('%n IN (%sql)', callback("$class::getPrimaryKey")->invoke() /*$class::getPrimaryKey()*/, (string) $sub); // $class::getDataSource()
		return new AssociatedCollection($ds, $class, $record);
	}


	/**
	 * Intersect entity name lazy getter.
	 * 
	 * @return string  intersect entity table name
	 */
	public function getIntersectEntity() {
		$class = $this->local;
		$database = callback("$class::getConnection")->invoke()->getDatabaseInfo(); //$class::getConnection()
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
	 * 
	 * @param  ActiveRecord $local
	 * @param  ActiveRecord|ActiveCollection|NULL $referenced
	 */
	public function saveReferenced(ActiveRecord $local, $referenced) {
		if ($referenced instanceof ActiveRecord)
			return $this->saveReferencedRecord($local, $referenced);
		else
			return $this->saveReferencedCollection($local, $referenced);
	}


	private function saveReferencedRecord(ActiveRecord $local, ActiveRecord $referenced) {
		// TODO: implement
		return $referenced;
	}

	
	private function saveReferencedCollection(ActiveRecord $local, ActiveCollection $referenced) {
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
			->where(array(array('%n IN %l', callback("$class::getForeignKey")->invoke() /*$class::getForeignKey()*/, $referenced->{callback("$class::getPrimaryKey")->invoke() /*$class::getPrimaryKey()*/})))
			->execute();

		// reload
		$class = $this->referenced;
		$pk = callback("$class::getPrimaryKey")->invoke();
		$where = array(array('%n IN %l', $pk, $referenced->$pk));
		return callback("$class::findAll")->invokeArgs(array('where' => $where)); //$class::findAll(array(array('%n IN %l', $pk, $referenced->$pk)));
	}
}