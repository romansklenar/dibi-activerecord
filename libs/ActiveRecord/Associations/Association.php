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


	/**
	 * Association constructor.
	 * 
	 * @param string $type  association type constant
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 * @param string $by  name of attribute in local object referring to referenced object
	 */
	public function __construct($type, $local, $referenced) {
		if (in_array($type, self::$types))
			$this->type = $type;
		else
			throw new InvalidArgumentException("Unknown association type '$type' given.");

		if ($type == self::HAS_MANY || $type == self::HAS_AND_BELONGS_TO_MANY)
			if (Inflector::isPlural($referenced))
				$referenced = Inflector::singularize($referenced);

		$r = new ClassReflection($referenced);
		if (!$r->isInstantiable())
			throw new InvalidArgumentException("Invalid class name '$referenced' of referenced object given.");

		$this->local = $local;
		$this->referenced = $referenced;
	}


	/**
	 * Is association in relation with given object name?
	 * @param string $referenced  referenced object name
	 * @return bool
	 */
	public function isInRelation($referenced) {
		return $referenced == $this->referenced;
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	abstract public function retreiveReferenced(ActiveRecord $record);

	
	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

}
