<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class HasManyAssociation extends Association {

	/** @var string */
	public $through;
	

	/**
	 * Association constructor.
	 *
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 * @param string $through
	 */
	public function __construct($local, $referenced, $through = NULL) {
		parent::__construct(self::HAS_MANY, $local, $referenced);

		if ($through !== NULL) {
			if (Inflector::isPlural($through))
				$through = Inflector::singularize($through);

			$r = new ClassReflection($through);
			if (!$r->isInstantiable())
				throw new InvalidArgumentException("Invalid class name '$through' of coupling object given.");
		}
		$this->through = $through;
	}


	/**
	 * Is association in relation with given object name?
	 *
	 * @param string $referenced  referenced object name
	 * @return bool
	 */
	public function isInRelation($referenced) {
		return parent::isInRelation($referenced) || $this->through == $referenced;
	}


	/**
	 * Retreives referenced object(s).
	 *
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		if ($this->through == NULL) {
			$class = $this->referenced;
			$key = $record->foreignKey;
			$types = $class::getTypes();
			$ds = $class::getDataSource()->where("%n = %{$types[$key]}", $key, $record[$record->primaryKey]);
			return new AssociatedCollection($ds, $class, $record);
			
		} else {
			$class = $this->referenced;
			$through = $this->through;
			$sub = $through::getDataSource()->select($class::getForeignKey())->where('%and', RecordHelper::formatForeignKey($record));
			$ds = $class::getDataSource()->where('%n IN (%sql)', $class::getPrimaryKey(), (string) $sub);
			return new AssociatedCollection($ds, $class, $record);
		}
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
		if ($this->through == NULL) {
			$referenced->{$local->foreignKey} = $local->{$local->primaryKey};
			return $referenced;
		} else {
			// TODO: implement
			return $referenced;
		}
	}


	private function saveReferencedCollection(ActiveRecord $local, ActiveCollection $referenced) {
		if ($this->through == NULL) {
			try {
				$old = $local->originals->{$this->getAttribute()};
				if ($old instanceof ActiveCollection) {
					$old->{$local->foreignKey} = NULL;
					$old->save();
				}

			} catch (ActiveRecordException $e) {
				if ($old instanceof ActiveCollection)
					$old->destroy();
			}

			// reload
			$class = $this->referenced;
			$referenced = $class::findAll(array(array('%n IN %l', $class::getPrimaryKey(), $referenced->{$class::getPrimaryKey()})));
			$referenced->{$local->foreignKey} = $local->{$local->primaryKey};
			return $referenced;

		} else {
			$through = new HasManyAssociation($this->local, $this->through);
			try {
				$old = $through->retreiveReferenced($local);

				if ($old instanceof ActiveCollection) {
					$old->{$local->foreignKey} = NULL;
					$old->save();
				}

			} catch (ActiveRecordException $e) {
				if ($old instanceof ActiveCollection)
					$old->destroy();
			}

			$through = new HasManyAssociation($this->referenced, $this->through);
			foreach ($referenced as $ref) {
				$new = $through->retreiveReferenced($ref);
				$new->{$local->foreignKey} = $local->{$local->primaryKey};
				$new->save();
			}

			// reload
			$class = $this->referenced;
			return $class::findAll(array(array('%n IN %l', $class::getPrimaryKey(), $referenced->{$class::getPrimaryKey()})));
		}
	}
}
