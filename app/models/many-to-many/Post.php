<?php


/**
 * @hasAndBelongsToMany(Tags)
 */
class Post extends ActiveRecord {
	protected static $connection = '#nette_style';
}