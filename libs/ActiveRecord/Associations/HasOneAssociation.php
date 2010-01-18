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
	 *
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$class = $this->referenced;
		$key = $record->foreignKey;
		$types = $class::getTypes();
		return $class::objects()->filter("%n = %{$types[$key]}", $key, $record[$record->primaryKey])->first();
	}


	/**
	 * Links referenced object to record.
	 * 
	 * @param  ActiveRecord $local
	 * @param  ActiveRecord|ActiveCollection|NULL $referenced
	 */
	public function saveReferenced(ActiveRecord $local, $referenced) {
		try {
			$old = $local->originals->{$this->getAttribute()};
			if ($old instanceof ActiveRecord) {
				$old->{$local->foreignKey} = NULL;
				$old->save();
			}

		} catch (ActiveRecordException $e) {
			if ($old instanceof ActiveRecord)
				$old->destroy();
		}

		$referenced->{$local->foreignKey} = $local->{$local->primaryKey};
		return $referenced;
	}
}
