<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
abstract class AssociationAnnotation extends Annotation {

	/** @var array */
	public $values = array();
	

	public function __construct(array $values) {
		foreach ($values as $k => $v) {
			$this->values[$k] = ltrim($v, '> ');
		}
	}

}
