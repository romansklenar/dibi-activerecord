<?php


/**
 * @hasOne(Car)
 * @belongsTo(belongs_to => Guide)
 */
class Guest extends ActiveRecord {
	protected static $connection = '#rails_style';
}