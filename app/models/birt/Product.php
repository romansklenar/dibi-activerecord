<?php


/**
 * Product active record model
 *
 * @belongsTo(OrderDetail, ProductLine)
 * @hasMany(through:OrderDetails => Orders)
 */
class Product extends ActiveRecord {

	protected static $foreing = '%primary%';
}
