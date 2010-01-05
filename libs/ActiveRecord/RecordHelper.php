<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class RecordHelper {

	/**
	 * @param  Record $record
	 * @param  array  $fields
	 * @return array
	 */
	public static function getValues(Record $record, array $fields = array()) {
		$output = array();
		foreach ($fields as $field)
			$output[$field] = $record->$field;
		return $output;
	}


	/**
	 * @param Record $record
	 * @param array  $input
	 */
	public static function setValues(Record $record, array $input = array()) {
		foreach ($input as $field => $value)
			$this->$field = $value;
	}
}