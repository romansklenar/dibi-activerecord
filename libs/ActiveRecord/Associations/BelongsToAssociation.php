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
	 *
	 * @param  ActiveRecord $record
	 * @return ActiveRecord|ActiveCollection|NULL
	 */
	public function retreiveReferenced(ActiveRecord $record) {
		$class = $this->referenced;
		$identifier = $record->{$this->referringAttribute};
		
		if ($identifier === NULL)
			return NULL;
		else
			return $class::find($identifier);
	}


	/**
	 * Links referenced object to record.
	 * 
	 * @param  ActiveRecord $local
	 * @param  ActiveRecord|ActiveCollection|NULL $referenced
	 */
	public function saveReferenced(ActiveRecord $local, $referenced) {
		try {
			$old = $referenced->{$referenced->getAssociation($local->class)->getAttribute()};
			if ($old instanceof ActiveRecord) {
				$old->{$referenced->foreignKey} = NULL;
				$old->save();
			}

		} catch (ActiveRecordException $e) {
			if ($old instanceof ActiveRecord)
				$old->destroy();
		}
		if ($referenced instanceof ActiveRecord)
			$local->{$referenced->foreignKey} = $referenced->{$referenced->primaryKey};

		// reload
		$class = $this->referenced;
		return $class::find($referenced->{$referenced->primaryKey});
	}
}
