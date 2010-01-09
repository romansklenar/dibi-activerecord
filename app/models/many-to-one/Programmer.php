<?php


/**
 * @hasMany(Tasks)
 * @hasMany(through:Task => Projects)
 */
class Programmer extends ActiveRecord {
	protected static $connection = '#nette_style';
}