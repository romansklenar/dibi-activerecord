<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class HasOneAssociation extends Association {

	/**
	 * Association constructor.
	 *
	 * @param string $local  local object name
	 * @param string $referenced  referenced object name
	 */
	public function __construct($local, $referenced) {
		parent::__construct(self::HAS_ONE, $local, $referenced);
	}


	/**
	 * Retreives referenced object(s).
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveRecordCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$key = $record->foreignMask;
		$referenced = new $this->referenced;
		$type = '%' . $referenced->types[$key];
		$class = $this->referenced;
		return $class::objects()->filter("%n = {$type}", $key, $record[$record->primaryName])->first();
	}
}
