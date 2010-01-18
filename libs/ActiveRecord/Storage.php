<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class Storage extends ArrayObject {

	/**
	 * @param  array
	 */
	public function __construct(array $input = array()) {
		parent::__construct($input, 2);
	}


	/**
	 * PHP < 5.3 workaround
	 * @return void
	 */
	public function __wakeup() {
		$this->setFlags(2);
	}
}