<?php


/**
 * @belongsTo(Programmer, Project)
 */
class Task extends ActiveRecord {
	protected static $connection = '#nette_style';
}