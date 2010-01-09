<?php

require_once __DIR__ . '/../ActiveRecordDatabaseTestCase.php';

/**
 * Test class for HasAndBelongsToManyAssociation.
 */
class HasAndBelongsToManyAssociationTest extends ActiveRecordDatabaseTestCase {
	
	public function testConstruct() {
		/**
		 * Manager is referenced by Employee (Employee has foreign key)
		 * Customer @hasMany(Order)
		 * Order @hasMany(Customer)
		 */
		Inflector::$railsStyle = FALSE;
		$singular = new HasAndBelongsToManyAssociation('Customer', 'Order'); // Customer @hasMany(Order)
		$plural   = new HasAndBelongsToManyAssociation('Customer', 'Orders'); // Customer @hasMany(Orders)
		$this->assertEquals($singular, $plural);

		$singular = new HasAndBelongsToManyAssociation('Order', 'Customer'); // Order @hasMany(Customer)
		$plural   = new HasAndBelongsToManyAssociation('Order', 'Customers'); // Order @hasMany(Customers)
		$this->assertEquals($singular, $plural);
	}

	public function testIsInRelation() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Customer', 'Order');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));

		$asc = new HasAndBelongsToManyAssociation('Customer', 'Orders');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));

		$asc = new HasAndBelongsToManyAssociation('Order', 'Customer');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));

		$asc = new HasAndBelongsToManyAssociation('Order', 'Customers');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));
	}

	public function testGetIntersectEntityByGivenManually() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product', 'OrderDetails');
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity('Order', 'Product', Mapper::getConnection()->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity('Product', 'Order', Mapper::getConnection()->getDatabaseInfo()));

		
		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Food', 'Ingredient', 'food_ingredients');
		$this->assertEquals('food_ingredients', $asc->getIntersectEntity('Food', 'Ingredient', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Ingredient', 'Food', 'food_ingredients');
		$this->assertEquals('food_ingredients', $asc->getIntersectEntity('Ingredient', 'Food', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

	}

	public function testGetIntersectEntityByAutodetect() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Programmer', 'Project');
		$this->assertEquals('ProjectsProgrammers', $asc->getIntersectEntity('Programmer', 'Project', Mapper::getConnection('#nette_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Project', 'Programmer');
		$this->assertEquals('ProjectsProgrammers', $asc->getIntersectEntity('Project', 'Programmer', Mapper::getConnection('#nette_style')->getDatabaseInfo()));


		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Food', 'Ingredient');
		$this->assertEquals('foods_ingredients', $asc->getIntersectEntity('Food', 'Ingredient', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Ingredient', 'Food');
		$this->assertEquals('foods_ingredients', $asc->getIntersectEntity('Ingredient', 'Food', Mapper::getConnection('#rails_style')->getDatabaseInfo()));
	}

	public function testFailsGetIntersectEntity() {
		Inflector::$railsStyle = FALSE;
		$this->setExpectedException('InvalidStateException');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product');
		$asc->getIntersectEntity('Order', 'Product', Mapper::getConnection()->getDatabaseInfo());
	}

	public function testRetreiveReferenced() {
		Inflector::$railsStyle = FALSE;
		$order = Order::find(10100);
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Products', 'OrderDetails');
		
		$ref = $asc->retreiveReferenced($order)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(4, $ref->count());
		$this->assertEquals('S18_1749', $ref->first()->productCode);
		$this->assertEquals('S24_3969', $ref->last()->productCode);

/*
SELECT Products.*
FROM Orders
JOIN OrderDetails USING (orderNumber)
JOIN Products USING (productCode)
WHERE Orders.orderNumber = 10100


SELECT Products.*
FROM Orders, OrderDetails, Products
WHERE Orders.orderNumber = 10100
    AND Orders.orderNumber = OrderDetails.orderNumber
    AND Products.productCode = OrderDetails.productCode


SELECT * FROM Products
WHERE productCode IN (SELECT productCode FROM OrderDetails WHERE orderNumber = 10100)

*/
		$product = Product::find('S10_1678');
		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Products', 'Orders', 'OrderDetails');

		$ref = $asc->retreiveReferenced($product)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(28, $ref->count());
		$this->assertEquals('10107', $ref->first()->orderNumber);
		$this->assertEquals('10417', $ref->last()->orderNumber);

/*
SELECT Orders.*
FROM Products
JOIN OrderDetails USING (productCode)
JOIN Orders USING (orderNumber)
WHERE Products.productCode = 'S10_1678'


SELECT Orders.*
FROM Orders, OrderDetails, Products
WHERE Products.productCode = 'S10_1678'
    AND Orders.orderNumber = OrderDetails.orderNumber
    AND Products.productCode = OrderDetails.productCode


SELECT * FROM Orders
WHERE orderNumber IN (SELECT orderNumber FROM OrderDetails WHERE productCode = 'S10_1678')

*/
	}
}