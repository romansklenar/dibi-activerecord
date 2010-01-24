<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class Association extends /*Nette\*/Object {

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

		if (Inflector::isPlural($referenced))
			$referenced = Inflector::singularize($referenced);
		
		if (Inflector::isPlural($local))
			$local = Inflector::singularize($local);

		if ($type == self::HAS_MANY || $type == self::HAS_AND_BELONGS_TO_MANY) {
			$this->attribute = lcfirst(Inflector::pluralize($referenced));
		} else {
			$this->attribute = lcfirst(Inflector::singularize($referenced));
		}

		$rc = new /*Nette\Reflection\*/ClassReflection($referenced);
		if (!$rc->isInstantiable())
			throw new InvalidArgumentException("Invalid class name '$referenced' of referenced object given.");

		$this->local = $local;
		$this->referenced = $referenced;
	}


	/**
	 * Is association in relation with given object name?
	 *
	 * @param string $class  referenced class name
	 * @return bool
	 */
	public function isInRelation($class) {
		return $class == $this->referenced;
	}


	/**
	 * Returns intersectional attribute name.
	 *
	 * @return string
	 */
	public function getAttribute() {
		return $this->attribute;
	}


	/**
	 * Retreives referenced object(s).
	 *
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveCollection|NULL
	 */
	abstract public function retreiveReferenced(ActiveRecord $record);


	/**
	 * Links referenced object to record.
	 *
	 * @param  ActiveRecord $record
	 * @param  ActiveRecord|ActiveCollection|NULL $new
	 */
	abstract public function saveReferenced(ActiveRecord $record, $new);


	/**
	 * Property getter.
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * Provides objects data type check.
	 *
	 * @return bool
	 */
	public function typeCheck($entry) {
		if ($this->type == self::HAS_MANY || $this->type == self::HAS_AND_BELONGS_TO_MANY)
			if (!$entry instanceof ActiveCollection)
				return FALSE;
			else 
				return $entry->itemType === $this->referenced;
		else
			return get_class($entry) === $this->referenced;
	}

}
