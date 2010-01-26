<?php


if (!function_exists('get_called_class')) {
	function get_called_class($bt = FALSE, $l = 1) {
		if (!$bt)
			$bt = debug_backtrace();
		if (!isset($bt[$l]))
			throw new Exception("Cannot find called class -> stack level too deep.");
		if (isset($bt[$l]['type'])) {
			switch ($bt[$l]['type']) {
				case '::':
					if (isset($bt[$l]['class']) && $bt[$l]['class'] == 'ObjectMixin') {
						return get_called_class($bt, $l+1);
					} else if (isset($bt[$l]['file']) && preg_match('/ObjectMixin.php$/', $bt[$l]['file'])) {
						return get_called_class($bt, $l+1);
					} else if (isset($bt[$l]['file'])) {
						$lines = file($bt[$l]['file']);
						preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $lines[$bt[$l]['line']-1], $matches);
					} else {
						return get_called_class($bt, $l+1);
					}
					
					if (!isset($matches[1])) {
						// must be an edge case.
						throw new Exception ("Could not find caller class: originating method call is obscured.");
					}
					switch ($matches[1]) {
						case 'self':
						case 'parent':
							return get_called_class($bt, $l+1);
						default:
							return $matches[1];
					}
				// won't get here.
				case '->':
					if (!is_object($bt[$l]['object']))
						throw new Exception ("Edge case fail. __get called on non object.");
					return get_class($bt[$l]['object']);
				default: throw new Exception ("Unknown backtrace method type");
			}
		} else if (isset($bt[$l]['function'])) { // callback
			$func = $bt[$l]['function'];
			if (preg_match('/^call_user_func/i', $func)) {
				if (count($bt[$l]['args'])) {
					preg_match('/([a-zA-Z0-9\_]+)::/', $bt[$l]['args'][0], $matches);

					if (!isset($matches[1])) {
						throw new Exception ("Could not find caller class: originating method call is obscured.");
					}

					if ($matches[1] != 'ActiveRecord')
						return $matches[1];
					else
						return get_called_class($bt, $l+1);

				} else {
					return get_called_class($bt, $l+1);
				}

			} else {
				throw new Exception("Cannot find called class from callback.");
			}
		}
	}
}


if (!function_exists('strip')) {

	/**
	 * Strips excess whitespaces of a string.
	 *
	 * @param  string  UTF-8 encoding
	 * @param  string
	 * @return string
	 */
	function strip($s, $charlist = " \t\n\r\0\x0B\xC2\xA0") {
		$charlist = preg_quote($charlist, '#');
		return trim(preg_replace('#['.$charlist.']['.$charlist.']+#u', ' ', $s));
	}
}


if (!function_exists('memory')) {

	/**
	 * Starts/stops count allocated memory.
	 *
	 * @param  string  name
	 * @return float   allocated memory in Bytes
	 */
	function memory($name = NULL) {
		static $memory = array();
		$now = memory_get_usage(); // or memory_get_peak_usage() ?
		$delta = isset($memory[$name]) ? $now - $memory[$name] : 0;
		$memory[$name] = $now;
		return $delta;
	}
}


if (!function_exists('timer')) {

	/**
	 * Starts/stops stopwatch.
	 *
	 * @param  string  name
	 * @return float   elapsed seconds
	 */
	function timer($name = NULL) {
		return /*Nette\*/Debug::timer($name);
	}
}


if (!function_exists('dump')) {

	/**
	 * Dumps information about a variable in readable format.
	 *
	 * @param  mixed  variable(s) to dump
	 * @return mixed  variable itself or dump
	 */
	function dump($var) {
		foreach ($args = func_get_args() as $arg)
			/*Nette\*/Debug::dump($arg);
		return $var;
	}
}


$r = new ReflectionClass('DibiColumnInfo');
if (!$r->hasMethod('isMandatory')) {
	DibiColumnInfo::extensionMethod('DibiColumnInfo::isMandatory', 'DibiColumnInfo_isMandatory');

	/**
	 * Detects if is column mandatory.
	 *
	 * @param DibiColumnInfo $_this
	 * @return bool
	 */
	function DibiColumnInfo_isMandatory(DibiColumnInfo $_this) {
		return !$_this->isNullable() && !$_this->isAutoIncrement() && $_this->getDefault() === NULL;
	}
}


define('FIRST', 'first');
define('LAST', 'last');
define('ALL', 'all');
