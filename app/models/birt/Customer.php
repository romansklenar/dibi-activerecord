<?php


/**
 * Customer active record model
 *
 * @belongsTo(salesRepEmployeeNumber => Employee)
 * @hasMany(Payments, Orders)
 */
class Customer extends ActiveRecord {

	protected static $foreingMask = '%primary%';
}
