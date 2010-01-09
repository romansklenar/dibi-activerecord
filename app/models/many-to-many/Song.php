<?php


/**
 * @hasAndBelongsToMany(Albums)
 */
class Song extends ActiveRecord {
	protected static $connection = '#rails_style';
}