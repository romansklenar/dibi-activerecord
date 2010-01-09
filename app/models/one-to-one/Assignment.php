<?php


/**
 * @belongsTo(Student)
 */
class Assignment extends ActiveRecord {
	protected static $connection = '#nette_style';
}