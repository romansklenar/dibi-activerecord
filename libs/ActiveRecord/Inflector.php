<?php


/**
 * The Inflector transforms words from singular to plural, class names to table names, modularized class names to ones without, and class names to foreign keys.
 * This solution is partitionaly based on Ruby on Rails ActiveSupport::Inflector (c) David Heinemeier Hansson. (http://rubyonrails.org), MIT license
 * @see http://api.rubyonrails.org/classes/Inflector.html
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @copyright  Copyright (c) 2008 Luke Baker (http://lukebaker.org)
 * @copyright  Copyright (c) 2005 Flinn Mueller (http://actsasflinn.com)
 * @license    New BSD License
 * @example    http://addons.nettephp.com/inflector
 * @package    Nette\Extras\Inflector
 * @version    0.3
 */
class Inflector {

	/** @var array  of plural nouns as rule => replacement */
	public static $plurals = array(
		'/(quiz)$/i' => '\1zes',
		'/^(ox)$/i' => '\1en',
		'/([m|l])ouse$/i' => '\1ice',
		'/(matr|vert|ind)(?:ix|ex)$/i' => '\1ices',
		'/(x|ch|ss|sh)$/i' => '\1es',
		'/([^aeiouy]|qu)y$/i' => '\1ies',
		'/(hive)$/i' => '\1s',
		'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
		'/sis$/i' => 'ses',
		'/([ti])um$/i' => '\1a',
		'/(buffal|tomat)o$/i' => '\1oes',
		'/(bu)s$/i' => '\1ses',
		'/(alias|status)$/i' => '\1es',
		'/(octop|vir)us$/i' => '\1i',
		'/(ax|test)is$/i' => '\1es',
		'/s$/i' => 's',
		'/$/' => 's',
	);

	/** @var array  of singular nouns as rule => replacement */
	public static $singulars = array(
		'/(database)s$/i' => '\1',
		'/(quiz)zes$/i' => '\1',
		'/(matr)ices$/i' => '\1ix',
		'/(vert|ind)ices$/i' => '\1ex',
		'/^(ox)en/i' => '\1',
		'/(alias|status)es$/i' => '\1',
		'/(octop|vir)i$/i' => '\1us',
		'/(cris|ax|test)es$/i' => '\1is',
		'/(shoe)s$/i' => '\1',
		'/(o)es$/i' => '\1',
		'/(bus)es$/i' => '\1',
		'/([m|l])ice$/i' => '\1ouse',
		'/(x|ch|ss|sh)es$/i' => '\1',
		'/(m)ovies$/i' => '\1ovie',
		'/(s)eries$/i' => '\1eries',
		'/([^aeiouy]|qu)ies$/i' => '\1y',
		'/([lr])ves$/i' => '\1f',
		'/(tive)s$/i' => '\1',
		'/(hive)s$/i' => '\1',
		'/([^f])ves$/i' => '\1fe',
		'/(^analy)ses$/i' => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/([ti])a$/i' => '\1um',
		'/(n)ews$/i' => '\1ews',
		'/s$/i' => '',
	);

	/** @var array  of irregular nouns */
	public static $irregular = array(
		'person' => 'people',
		'man' => 'men',
		'child' => 'children',
		'sex' => 'sexes',
		'move' => 'moves',
		'cow' => 'kine',
	);

	/** @var array  of uncountable nouns */
	public static $uncountable = array(
		'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep',
	);


	/**
	 * The reverse of pluralize, returns the singular form of a word.
	 * @param string $word
	 * @return string
	 */
	public static function singularize($word) {
		$lower = String::lower($word);

		if (self::isUncountable($word))
			return $word;

		if (self::isIrregular($word))
			foreach (self::$irregular as $single => $plural)
				if ($lower == $plural)
					return $single;

		foreach (self::$singulars as $rule => $replacement)
			if (preg_match($rule, $word))
				return preg_replace($rule, $replacement, $word);

		return FALSE;
	}


	/**
	 * Returns the plural form of the word.
	 * @param string $word
	 * @return string
	 */
	public static function pluralize($word) {
		$lower = String::lower($word);

		if (self::isUncountable($word))
			return $word;

		if (self::isIrregular($word))
			foreach (self::$irregular as $single => $plural)
				if ($lower == $single)
					return $plural;

		foreach (self::$plurals as $rule => $replacement)
			if (preg_match($rule, $word))
				return preg_replace($rule, $replacement, $word);

		return FALSE;
	}


	/**
	 * Is given string singular noun?
	 * @param string $word
	 * @return bool
	 */
	public static function isSingular($word) {
		if (self::isUncountable($word))
			return TRUE;

		return !self::isPlural($word);
	}


	/**
	 * Is given string plural noun?
	 * @param string $word
	 * @return bool
	 */
	public static function isPlural($word) {
		if (self::isUncountable($word))
			return TRUE;

		return self::singularize($word) !== FALSE;
	}


	/**
	 * Is given string regular noun?
	 * @param string $word
	 * @return bool
	 */
	private static function isRegular($word) {
		$word = String::lower($word);
		return (bool) !in_array($word, self::$irregular) && !array_key_exists($word, self::$irregular);
	}


	/**
	 * Is given string countable noun?
	 * @param string $word
	 * @return bool
	 */
	private static function isCountable($word) {
		$word = String::lower($word);
		return (bool) !in_array($word, self::$uncountable);
	}


	/**
	 * Is given string irregular noun?
	 * @param string $word
	 * @return bool
	 */
	private static function isIrregular($word) {
		return !self::isRegular($word);
	}


	/**
	 * Is given string uncountble noun?
	 * @param string $word
	 * @return bool
	 */
	private static function isUncountable($word) {
		return !self::isCountable($word);
	}


	/**
	 * Ordinalize turns a number into an ordinal string used to denote
	 * the position in an ordered sequence such as 1st, 2nd, 3rd, 4th.
	 * @param int $number
	 * @return string
	 */
	public static function ordinalize($number) {
		$number = (int) $number;

		if ($number % 100 >= 11 && $number % 100 <= 13)
			return "{$number}th";
		else
			switch ($number % 10) {
				case 1: return "{$number}st";
				case 2: return "{$number}nd";
				case 3: return "{$number}rd";
				default: return "{$number}th";
			}
	}


	/**
	 * By default, camelize() converts strings to UpperCamelCase.
	 * If the second argument is set to FALSE then camelize() produces lowerCamelCase.
	 * camelize() will also convert '/' to '\' which is useful for converting paths to namespaces.
	 *
	 * @param string $word  lower case and underscored word
	 * @param bool   $firstUpper  first letter in uppercase?
	 * @return string
	 */
	public static function camelize($word, $firstUpper = TRUE) {
		$word = preg_replace(array('/(^|_)(.)/e', '/(\/)(.)/e'), array("strtoupper('\\2')", "strtoupper('\\\2')"), strval($word));
		return $firstUpper ? ucfirst($word) : lcfirst($word);
	}


	/**
	 * Replaces underscores with dashes in the string.
	 *
	 * @param  string $word  underscored word
	 * @return string
	 */
	public static function dasherize($word) {
		return preg_replace('/_/', '-', strval($word));
	}


	/**
	 * Capitalizes all the words and replaces some characters in the string to create a nicer looking title.
	 * Titleize() is meant for creating pretty output.
	 *
	 * @param  string $word  underscored word
	 * @return string
	 */
	public static function titleize($word) {
		return preg_replace("/\b('?[a-z])/", "ucfirst('\\1')", self::humanize(self::underscore($word)));
	}


	/**
	 * The reverse of camelize(). Makes an underscored form from the expression in the string.
	 * Changes '::' to '/' to convert namespaces to paths.
	 *
	 * @param string $word  camel cased word
	 * @return string
	 */
	public static function underscore($word) {
		return strtolower(preg_replace('/([A-Z]+)([A-Z])/','\1_\2', preg_replace('/([a-z\d])([A-Z])/','\1_\2', strval($word))));
	}


	/**
	 * Capitalizes the first word and turns underscores into spaces and strips _id.
	 * Like titleize(), this is meant for creating pretty output.
	 *
	 * @param string $word  lower case and underscored word
	 * @return string
	 */
	public static function humanize($word) {
		return ucfirst(strtolower(preg_replace(array('/_id$/', '/_/'), array('', ' '), $word)));
	}


	/**
	 * Removes the namespace part from the expression in the string.
	 *
	 * @param  string $class  class name in namespace
	 * @return string
	 */
	public static function demodulize($class) {
		$class = ltrim(strval($class), '\\');
		if ($a = strrpos($class, '\\'))
			$class = substr($class, $a+1);
		return preg_replace('/^.*::/', '', $class);
	}
	

	/**
	 * Create the name of a table like Rails does for models to table names.
	 * This method uses the pluralize method on the last word in the string.
	 *
	 * @param  string $class  class name
	 * @return string
	 */
	public static function tableize($class, $camelize = FALSE) {
		$table = self::pluralize($class);
		return $camelize ? self::camelize($table) : self::underscore($table);
	}


	/**
	 * Create a class name from a plural table name like Rails does for table names to models.
	 * Note that this returns a string and not a Class.
	 * To convert to an actual class follow classify() with constantize().
	 *
	 * @param  string $table  table name
	 * @return string
	 */
	public static function classify($table) {
		return self::camelize(self::singularize($table));
	}


	/**
	 * Creates a foreign key name from a class name.
	 * Second parametr sets whether the method should put '_' between the name and 'id'/'Id'.
	 *
	 * @param  string $class     class name
	 * @param  bool   $separete  separate class name and id with underscore?
	 * @return string
	 */
	public static function foreignKey($class, $separete = TRUE) {
		return self::underscore((self::isPlural($class) ? self::singularize($class) : self::demodulize($class))) . ($separete ? "_id" : "Id");
	}
}