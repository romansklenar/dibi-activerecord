<?php


/**
 * ProductLine active record model
 *
 * @hasMany(Products)
 */
class ProductLine extends ActiveRecord {

	protected static $foreingMask = '%primary%';
}
