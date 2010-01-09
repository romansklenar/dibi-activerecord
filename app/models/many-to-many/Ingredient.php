<?php


/** @hasAndBelongsToMany(Food) */
class Ingredient extends ActiveRecord {
	protected static $connection = '#rails_style';
}