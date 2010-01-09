<?php


/**
 * @belongsTo(Food, Ingredient)
 */
class Composition extends ActiveRecord {
	protected static $connection = '#rails_style';
}