<?php


/** @hasAndBelongsToMany(Programmers) */
class Project extends ActiveRecord {
	protected static $connection = '#nette_style';
}