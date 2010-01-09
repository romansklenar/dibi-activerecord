<?php


/**
 * @hasMany(Compositions)
 * @hasMany(through:Compositions => Ingredients)
 */
class Food extends ActiveRecord {
	protected static $connection = '#rails_style';
}