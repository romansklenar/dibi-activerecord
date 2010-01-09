<?php


/**
 * @hasAndBelongsToMany(Songs)
 */
class Album extends ActiveRecord {
	protected static $connection = '#rails_style';
}