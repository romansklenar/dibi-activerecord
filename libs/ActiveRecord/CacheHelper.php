<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class CacheHelper extends Object {

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct() {
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}


	/**
	 * @return Cache
	 */
	final public static function getCache() {
		return Environment::getCache('Dibi.ActiveRecod');
	}


	/**
	 * @return void
	 */
	final public static function cleanCache() {
		self::getCache()->clean();
	}
}