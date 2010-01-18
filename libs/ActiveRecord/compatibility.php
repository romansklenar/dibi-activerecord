<?php


if (!function_exists('array_replace')) {
	
	/**
	 * Replaces elements from passed arrays into the first array
	 * @see http://php.net/manual/en/function.array-replace.php
	 *
	 * @param array $array  The array in which elements are replaced.
	 * @param array $array1 The array from which elements will be extracted.
	 * @return array
	 */
	function array_replace(array &$array, array &$array1) {
		$args = func_get_args();
		$count = func_num_args();

		for ($i = 1; $i < $count; $i++) {
			if (is_array($args[$i])) {
				foreach ($args[$i] as $key => $val)
					$array[$key] = $val;
			}
			else {
				trigger_error(__FUNCTION__ . '(): Argument #' . ($i+1) . ' is not an array', E_USER_WARNING);
				return NULL;
			}
		}
		return $array;
	}
}


if (!function_exists('array_interlace')) {

	/**
	 * Replaces elements from passed arrays into the first array
	 * @see http://php.net/manual/en/function.array-replace.php
	 *
	 * @param array $array  The array in which elements are replaced.
	 * @return array
	 */
	function array_interlace(array $args) {
		//$args = func_get_args();
		$count = count($args);

		if ($count < 2)
			return FALSE;

		$i = 0;
		$j = 0;
		$arr = array();

		foreach($args as $arg) {
			foreach($arg as $v) {
				$arr[$j] = $v;
				$j += $count;
			}

			$i++;
			$j = $i;
		}

		ksort($arr);
		return array_values($arr);
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
		return /*\Nette\*/Debug::timer($name);
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
			Debug::dump($arg);
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


const FIRST = 'first';
const LAST = 'last';
const ALL = 'all';
