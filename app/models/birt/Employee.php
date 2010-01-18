<?php


/**
 * Employee active record model
 *
 * @belongsTo(Office, reportsTo => Manager)
 * @hasMany(Customers)
 */
class Employee extends ActiveRecord {

	protected static $foreing = '%primary%';
}
