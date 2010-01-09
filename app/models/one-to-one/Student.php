<?php


/**
 * @hasOne(Assignment)
 * @belongsTo(reportsTo => Supervisor)
 */
class Student extends ActiveRecord {
	protected static $connection = '#nette_style';
}