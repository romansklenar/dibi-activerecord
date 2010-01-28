<?php


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


if (!function_exists('formatMemory')) {

	/**
	 * Formats memory to human readable format.
	 *
	 * @param int $bytes
	 * @return string
	 */
	function formatMemory($bytes) {
		return number_format($bytes / 1024, 1, ',', ' ') . ' kB';
	}
}


if (!function_exists('formatTime')) {

	/**
	 * Formats time to human readable format.
	 *
	 * @param int $seconds
	 * @return string
	 */
	function formatTime($seconds) {
		return number_format($seconds * 1000, 1, ',', ' ') . ' ms';
	}
}


const FIRST = 'first';
const LAST = 'last';
const ALL = 'all';
