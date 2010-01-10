<?php


/**
 * Manager active record model
 *
 * @belongsTo(Office)
 * @hasMany(Customers, Employees)
 */
class Manager extends Employee {

	protected static $primary = 'employeeNumber';
	protected static $foreingMask = '%primary%';
}
