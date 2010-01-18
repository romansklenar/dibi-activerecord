<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
interface IValidator
{
	/**
	 * Proccess validation.
	 *
	 * @return void
	 */
	public function validate();


	/**
	 * Adds error message to the list.
	 *
	 * @param  string  error message
	 * @return void
	 */
	public function addError($message);


	/**
	 * Returns validation errors.
	 *
	 * @return array
	 */
	public function getErrors();


	/**
	 * @return bool
	 */
	public function hasErrors();


	/**
	 * @return void
	 */
	public function cleanErrors();
}