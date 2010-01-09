<?php


/** @hasAndBelongsToMany(Projects) */
class Programmer extends ActiveRecord {
	protected static $connection = '#nette_style';
}