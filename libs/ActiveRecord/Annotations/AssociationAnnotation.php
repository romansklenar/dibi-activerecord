<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class AssociationAnnotation extends Annotation {

	/** @var array */
	protected $values = array();
	

	/**
	 * Object constructor.
	 * @return void
	 */
	public function __construct(array $values) {
		if (count($values) == 1 && array_key_exists('value', $values))
			$values = array($values['value']);

		foreach ($values as $k => $v) {
			$this->values[$k] = ltrim($v, '> ');
		}
	}


	/**
	 * Property getter.
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

}
