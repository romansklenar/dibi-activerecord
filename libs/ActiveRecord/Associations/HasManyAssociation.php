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
	 * @param string $referenced  referenced object name
	 * @return bool
	 */
	public function isInRelation($referenced) {
		return parent::isInRelation($referenced) || $this->through == $referenced;
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		if ($this->through == NULL) {
			$key = $record->foreignKey;
			$referenced = new $this->referenced;
			$type = '%' . $referenced->types[$key];
			$class = $this->referenced;
			return $class::objects()->filter("%n = {$type}", $key, $record[$record->primaryKey]);
			
		} else {
			$referenced = new $this->referenced;
			$through = new $this->through;
			$sub = $through->dataSource->select($referenced->foreignKey)->where('%and', RecordHelper::formatForeignKey($record));
			$ds = $referenced->dataSource->where('%n IN (%sql)', $referenced->primaryKey, (string) $sub);
			return new ActiveRecordCollection($ds, $this->referenced);
		}
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
