<?php


/**
 * @hasMany(Compositions)
 * @hasMany(through:Compositions => Foods)
 */
class Ingredient extends ActiveRecord {
	protected static $connection = '#rails_style';
}