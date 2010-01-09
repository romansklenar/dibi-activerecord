<?php

require_once __DIR__ . '/BirtBaseTestCase.php';

/**
 * Test class for Product.
 */
class ProductTest extends BirtBaseTestCase {

	/** @var Product */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Product;
	}


	public function testRelationOrders() {
		$product = Product::find('S10_1678');
		$this->assertTrue($product->orders instanceof ActiveRecordCollection);
		$this->assertEquals(28, count($product->orders));
		$this->assertTrue(($order = $product->orders->first()) instanceof Order);
		$this->assertEquals(10107, $order->orderNumber);
		$this->assertTrue(($order = $product->orders->last()) instanceof Order);
		$this->assertEquals(10417, $order->orderNumber);
	}	
}