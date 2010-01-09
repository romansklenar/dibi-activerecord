<?php


/**
 * @hasAndBelongsToMany(Posts)
 */
class Tag extends ActiveRecord {
	protected static $connection = '#nette_style';
}