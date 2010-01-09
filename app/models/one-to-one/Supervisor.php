<?php


/**
 * @hasMany(Students)
 */
class Supervisor extends ActiveRecord {
	protected static $connection = '#nette_style';
	protected static $primary = 'id';
}