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
	const EQUAL = Form::EQUAL;
	const IS_IN = Form::IS_IN;
	const FILLED = Form::FILLED;
	const VALID = Form::VALID;

	/**#@+ validation rule name */
	const MIN_LENGTH = Form::MIN_LENGTH;
	const MAX_LENGTH = Form::MAX_LENGTH;
	const LENGTH = Form::LENGTH;
	const EMAIL = Form::EMAIL;
	const URL = Form::URL;
	const REGEXP = Form::REGEXP;
	const INTEGER = Form::INTEGER;
	const NUMERIC = Form::NUMERIC;
	const FLOAT = Form::FLOAT;
	const RANGE = Form::RANGE;


	/**#@+ operation name /
	const EQUAL = ':equal';
	const IS_IN = ':equal';
	const FILLED = ':filled';
	const VALID = ':valid';

	// text
	const MIN_LENGTH = ':minLength';
	const MAX_LENGTH = ':maxLength';
	const LENGTH = ':length';
	const EMAIL = ':email';
	const URL = ':url';
	const REGEXP = ':regexp';
	const INTEGER = ':integer';
	const NUMERIC = ':integer';
	const FLOAT = ':float';
	const RANGE = ':range';
	*/


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


	/**
	 * Form rules generator.
	 *
	 * @param ActiveRecord $record
	 * @param Form $form
	 * @return Form
	 */
	public static function generateRules(ActiveRecord $record, Form $form) {
		throw new NotImplementedException;
		
		// generate rules
		return $form;
	}
}
