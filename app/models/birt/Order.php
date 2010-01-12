<?php


/**
 * Order active record model
 *
 * @belongsTo(Customer, OrderDetail)
 * @hasMany(:OrderDetails => Products)
 */
class Order extends ActiveRecord {

	protected static $foreing = '%primary%';
}
