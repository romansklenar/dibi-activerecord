<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class Association extends Object {

	/**#@+ association type */
	const BELONGS_TO = 'belongsTo'; // N:1
	const HAS_ONE = 'hasOne'; // 1:1
	const HAS_MANY = 'hasMany'; // 1:N
	const HAS_AND_BELONGS_TO_MANY = 'hasAndBelongsToMany'; // M:N

	/** @var string */
	public $type;

	/** @var array */
	static public $types = array(self::BELONGS_TO, self::HAS_ONE, self::HAS_MANY, self::HAS_AND_BELONGS_TO_MANY);

	/** @var string */
	public $local;

	/** @var string */
	public $referenced;

	/** @var string */
	protected $attribute;


	/**
	 * Association constructor.
	 * 
	 * @param string $type  association type constant
	 * @param string $local  local class name
	 * @param string $referenced  referenced class name
	 */
	public function __construct($type, $local, $referenced) {
		if (in_array($type, self::$types))
			$this->type = $type;
		else
			throw new InvalidArgumentException("Unknown association type '$type' given.");

		if ($type == self::HAS_MANY || $type == self::HAS_AND_BELONGS_TO_MANY) {
			$this->attribute = lcfirst(Inflector::pluralize($referenced));
			if (Inflector::isPlural($referenced))
				$referenced = Inflector::singularize($referenced);
		} else {
			$this->attribute = lcfirst(Inflector::singularize($referenced));
		}

		$rc = new ClassReflection($referenced);
		if (!$rc->isInstantiable())
			throw new InvalidArgumentException("Invalid class name '$referenced' of referenced object given.");

		$this->local = $local;
		$this->referenced = $referenced;
	}


	/**
	 * Is association in relation with given object name?
	 * @param string $class  referenced class name
	 * @return bool
	 */
	public function isInRelation($class) {
		return $class == $this->referenced;
	}


	/**
	 * Returns intersectional attribute name.
	 * @return string
	 */
	public function getAttribute() {
		return $this->attribute;
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	abstract public function retreiveReferenced(ActiveRecord $record);


	/**
	 * Links referenced object to record.
	 * @param  ActiveRecord $record
	 * @param  ActiveRecord|ActiveRecordCollection|NULL $new
	 */
	abstract public function saveReferenced(ActiveRecord $record, $new);


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @return bool
	 */
	public function typeCheck($entry) {
		if ($this->type == self::HAS_MANY || $this->type == self::HAS_AND_BELONGS_TO_MANY)
			if (!$entry instanceof ActiveRecordCollection)
				return FALSE;
			else 
				return $entry->itemType === $this->referenced;
		else
			return get_class($entry) === $this->referenced;
	}

}
