<?php

require_once __DIR__ . '/BirtBaseTestCase.php';

/**
 * Test class for Order.
 */
class OrderTest extends BirtBaseTestCase {

	/** @var Order */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Order;
	}


	public function testRelationProducts() {
		$order = Order::find(10100);
		$this->assertTrue($order->products instanceof ActiveRecordCollection);
		$this->assertEquals(4, count($order->products));
		$this->assertTrue(($product = $order->products->first()) instanceof Product);
		$this->assertEquals('S18_1749', $product->productCode);
		$this->assertTrue(($product = $order->products->last()) instanceof Product);
		$this->assertEquals('S24_3969', $product->productCode);
	}	
}