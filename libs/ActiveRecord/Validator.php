<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class Validator extends Object
{
	/**#@+ operation name */
	const EQUAL = ':equal';				// Form::EQUAL
	const IS_IN = ':equal';				// Form::IS_IN
	const FILLED = ':filled';			// Form::FILLED
	const VALID = ':valid';				// Form::VALID
	const MIN_LENGTH = ':minLength';	// Form::MIN_LENGTH
	const MAX_LENGTH = ':maxLength';	// Form::MAX_LENGTH
	const LENGTH = ':length';			// Form::LENGTH
	const EMAIL = ':email';				// Form::EMAIL
	const URL = ':url';					// Form::URL
	const REGEXP = ':regexp';			// Form::REGEXP
	const INTEGER = ':integer';			// Form::INTEGER
	const NUMERIC = ':integer';			// Form::NUMERIC
	const FLOAT = ':float';				// Form::FLOAT
	const RANGE = ':range';				// Form::RANGE
	/**#@-*/


	/**
	 * Provides record validation.
	 *
	 * @param ActiveRecord $record
	 * @throws ValidateException
	 * @return void
	 */
	public function validate(ActiveRecord $record) {
		throw new NotImplementedException;
	}
}
