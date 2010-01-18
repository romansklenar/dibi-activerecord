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
		$this->assertType('ActiveCollection', $order->products);
		$this->assertEquals(4, count($order->products));
		$this->assertType('Product', $product = $order->products->first());
		$this->assertEquals('S18_1749', $product->productCode);
		$this->assertType('Product', $product = $order->products->last());
		$this->assertEquals('S24_3969', $product->productCode);
	}	
}