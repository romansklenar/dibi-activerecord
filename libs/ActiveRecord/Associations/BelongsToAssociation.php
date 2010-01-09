<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class BelongsToAssociation extends Association {

	/** @var DibiColumnInfo|array  referring attribute column info */
	public $referringAttribute;

	
	/**
	 * Association constructor.
	 *
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 * @param string $by  name of attribute in local object referring to referenced object
	 */
	public function __construct($local, $referenced, $referringAttribute = NULL) {
		parent::__construct(self::BELONGS_TO, $local, $referenced);
		$this->referringAttribute = $referringAttribute;
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		if ($this->referringAttribute !== NULL) {
			$key = $this->referringAttribute;
		} else {
			$referenced = new $this->referenced;
			$key = $referenced->primaryName;
		}
		$class = $this->referenced;
		return $class::find($record->$key);
	}
}
