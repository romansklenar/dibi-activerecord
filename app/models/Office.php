<?php


/**
 * Office active record model
 *
 * @hasMany(Employees)
 */
class Office extends ActiveRecord {

	protected static $foreingMask = '%primary%';
}
