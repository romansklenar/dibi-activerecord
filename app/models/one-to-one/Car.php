<?php


/** @belongsTo(Guest) */
class Car extends ActiveRecord {
	protected static $connection = '#rails_style';
}