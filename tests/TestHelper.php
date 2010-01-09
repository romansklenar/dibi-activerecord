<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class TestHelper {

	public static function isPhpVersion($version) {
		switch ($version) {
			case '5.2': return version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.0', '<');
			case '5.3': return version_compare(PHP_VERSION, '5.3.0', '>=');
			default: throw new InvalidArgumentException("Unsupported argument '$version' given. Only '5.2' and '5.3' are supported.");
		}
	}
}