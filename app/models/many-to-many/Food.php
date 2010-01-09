<?php


/** @hasAndBelongsToMany(Ingredients) */
class Food extends ActiveRecord {
	protected static $connection = '#rails_style';
}