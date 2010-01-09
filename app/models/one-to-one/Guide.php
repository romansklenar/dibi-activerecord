<?php


/** @hasMany(Guests) */
class Guide extends ActiveRecord {
	protected static $connection = '#rails_style';
}