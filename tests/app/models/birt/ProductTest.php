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

	public function testStaticFind() {
		$products = Product::find('S10_1678', 'S24_2000');
		$this->assertType('ActiveRecordCollection', $products);
		$this->assertEquals('S10_1678', $products->first()->productCode);
		$this->assertEquals('S24_2000', $products->last()->productCode);
	}


	public function testRelationOrders() {
		$product = Product::find('S10_1678');
		$this->assertType('ActiveRecordCollection', $product->orders);
		$this->assertEquals(28, count($product->orders));
		$this->assertType('Order', $order = $product->orders->first());
		$this->assertEquals(10107, $order->orderNumber);
		$this->assertType('Order', $order = $product->orders->last());
		$this->assertEquals(10417, $order->orderNumber);
	}	
}