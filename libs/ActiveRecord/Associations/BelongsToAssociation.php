<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class BelongsToAssociation extends Association {

	/** @var string|array  referring attribute name */
	public $referringAttribute;

	
	/**
	 * Association constructor.
	 *
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 * @param string $referringAttribute  name of attribute in local object referring to referenced object
	 */
	public function __construct($local, $referenced, $referringAttribute = NULL) {
		parent::__construct(self::BELONGS_TO, $local, $referenced);

		if ($referringAttribute === NULL) {
			$this->referringAttribute = $referenced::getForeignKey();
		} else {
			$this->referringAttribute = $referringAttribute;
		}
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$key = $this->referringAttribute;
		$class = $this->referenced;
		return $class::find($record->$key);
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
