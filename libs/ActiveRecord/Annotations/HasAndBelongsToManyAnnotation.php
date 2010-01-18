<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
final class HasAndBelongsToManyAnnotation extends AssociationAnnotation {

	/**
	 * Object constructor.
	 * @return void
	 */
	public function __construct(array $values) {
		if (count($values) == 1 && array_key_exists('value', $values))
			$values = array($values['value']);

		foreach ($values as $k => $v) {
			if (is_numeric($k) && strpos($v, ':') !== FALSE) {
				$parts = explode('=', $v);
				$joinTable = trim($parts[0]);
				$v = $parts[1];
				$k = substr($joinTable, strpos($joinTable, ':')+1);
			}
			$v = trim($v, '> ');
			if (is_numeric($k))
				$this->values[] = $v;
			else
				$this->values[$k] = $v;
		}
	}
}
