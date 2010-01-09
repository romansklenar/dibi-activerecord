<?php


/**
 * @hasMany(Tasks)
 * @hasMany(through:Task => Programmers)
 */
class Project extends ActiveRecord {
	protected static $connection = '#nette_style';
}