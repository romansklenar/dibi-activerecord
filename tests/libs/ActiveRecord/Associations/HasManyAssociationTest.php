<?php

require_once __DIR__ . '/../ActiveRecordDatabaseTestCase.php';

/**
 * Test class for Association.
 */
class HasManyAssociationTest extends ActiveRecordDatabaseTestCase {


	public function testConstruct() {
		$singular = new HasManyAssociation('Manager', 'Employee'); // Manager @hasMany(Employee)
		$plural   = new HasManyAssociation('Manager', 'Employees');  // Manager @hasMany(Employees)
		$this->assertEquals($singular, $plural);
		$this->assertEquals('Manager', $singular->local);
		$this->assertEquals('Employee', $singular->referenced);
		$this->assertEquals(NULL, $singular->through);

		$singular = new HasManyAssociation('Order', 'Customer'); // Order @hasMany(Customer)
		$plural   = new HasManyAssociation('Order', 'Customers'); // Order @hasMany(Customers)
		$this->assertEquals($singular, $plural);
		$this->assertEquals('Order', $singular->local);
		$this->assertEquals('Customer', $singular->referenced);
		$this->assertEquals(NULL, $singular->through);

		$singular = new HasManyAssociation('Order', 'Product', 'OrderDetail'); // Order @hasMany(through:OrderDetail => Product)
		$plural   = new HasManyAssociation('Order', 'Products', 'OrderDetails'); // Order @hasMany(through:OrderDetail => Products)
		$this->assertEquals($singular, $plural);
		$this->assertEquals('Order', $singular->local);
		$this->assertEquals('Product', $singular->referenced);
		$this->assertEquals('OrderDetail', $singular->through);
	}

	public function testIsInRelation() {
		$asc = new HasManyAssociation('Office', 'Employee');
		$this->assertTrue($asc->isInRelation('Employee'));
		$this->assertFalse($asc->isInRelation('Office'));

		$asc = new HasManyAssociation('Office', 'Employees');
		$this->assertTrue($asc->isInRelation('Employee'));
		$this->assertFalse($asc->isInRelation('Office'));


		$asc = new HasManyAssociation('Customer', 'Order');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));

		$asc = new HasManyAssociation('Customer', 'Orders');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));
		

		$asc = new HasManyAssociation('Order', 'Customer');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));

		$asc = new HasManyAssociation('Order', 'Customers');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));
	}

	public function testRetreiveReferenced() {
		Inflector::$railsStyle = FALSE;
		
		$office = Office::find(1);
		$asc = new HasManyAssociation('Office', 'Employee');
		$ref = $asc->retreiveReferenced($office)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(6, $ref->count());
		$this->assertTrue($ref->first() instanceof Employee);

		$employee = Employee::find(1370);
		$asc = new HasManyAssociation('Employee', 'Customer');
		$asc = new HasManyAssociation('Employee', 'Customers');
		$ref = $asc->retreiveReferenced($employee)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(7, $ref->count());
		$this->assertTrue($ref->first() instanceof Customer);



		$programmer = Programmer::find(4);
		$asc = new HasManyAssociation('Programmer', 'Task');
		$asc = new HasManyAssociation('Programmer', 'Tasks');
		$ref = $asc->retreiveReferenced($programmer)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(2, $ref->count());
		$this->assertTrue($ref->first() instanceof Task);

		$project = Project::find(1);
		$asc = new HasManyAssociation('Project', 'Task');
		$asc = new HasManyAssociation('Project', 'Tasks');
		$ref = $asc->retreiveReferenced($project)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(5, $ref->count());
		$this->assertTrue($ref->first() instanceof Task);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$asc = new HasManyAssociation('Food', 'Composition');
		$asc = new HasManyAssociation('Food', 'Compositions');
		$ref = $asc->retreiveReferenced($food)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(6, $ref->count());
		$this->assertTrue($ref->first() instanceof Composition);

		$ingredient = Ingredient::find(7);
		$asc = new HasManyAssociation('Ingredient', 'Composition');
		$asc = new HasManyAssociation('Ingredient', 'Compositions');
		$ref = $asc->retreiveReferenced($ingredient)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(3, $ref->count());
		$this->assertTrue($ref->first() instanceof Composition);
	}

	public function testRetreiveReferencedViaThrough() {
		Inflector::$railsStyle = FALSE;

		$order = Order::find(10100);
		$asc = new HasManyAssociation('Order', 'Product', 'OrderDetail');
		$asc = new HasManyAssociation('Order', 'Products', 'OrderDetail');
		$ref = $asc->retreiveReferenced($order)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(4, $ref->count());
		$this->assertTrue($ref->first() instanceof Product);
		$this->assertEquals('S18_1749', $ref->first()->productCode);
		$this->assertTrue($ref->last() instanceof Product);
		$this->assertEquals('S24_3969', $ref->last()->productCode);

		$product = Product::find('S10_1678');
		$asc = new HasManyAssociation('Product', 'Order', 'OrderDetail');
		$asc = new HasManyAssociation('Product', 'Orders', 'OrderDetail');
		$ref = $asc->retreiveReferenced($product)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(28, $ref->count());
		$this->assertTrue($ref->first() instanceof Order);
		$this->assertEquals(10107, $ref->first()->orderNumber);
		$this->assertTrue($ref->last() instanceof Order);
		$this->assertEquals(10417, $ref->last()->orderNumber);



		$programmer = Programmer::find(4);
		$asc = new HasManyAssociation('Programmer', 'Project', 'Task');
		$asc = new HasManyAssociation('Programmer', 'Projects', 'Tasks');
		$ref = $asc->retreiveReferenced($programmer)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(2, $ref->count());
		$this->assertTrue($ref->first() instanceof Project);
		$this->assertEquals(1, $ref->first()->id);
		$this->assertTrue($ref->last() instanceof Project);
		$this->assertEquals(3, $ref->last()->id);

		$project = Project::find(1);
		$asc = new HasManyAssociation('Project', 'Programmer', 'Task');
		$asc = new HasManyAssociation('Project', 'Programmers', 'Tasks');
		$ref = $asc->retreiveReferenced($project)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(5, $ref->count());
		$this->assertTrue($ref->first() instanceof Programmer);
		$this->assertEquals(1, $ref->first()->id);
		$this->assertTrue($ref->last() instanceof Programmer);
		$this->assertEquals(7, $ref->last()->id);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$asc = new HasManyAssociation('Food', 'Ingredient', 'Composition');
		$asc = new HasManyAssociation('Food', 'Ingredients', 'Compositions');
		$ref = $asc->retreiveReferenced($food)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(6, $ref->count());
		$this->assertTrue($ref->first() instanceof Ingredient);
		$this->assertEquals(2, $ref->first()->id);
		$this->assertTrue($ref->last() instanceof Ingredient);
		$this->assertEquals(8, $ref->last()->id);

		$ingredient = Ingredient::find(7);
		$asc = new HasManyAssociation('Ingredient', 'Food', 'Composition');
		$asc = new HasManyAssociation('Ingredient', 'Foods', 'Compositions');
		$ref = $asc->retreiveReferenced($ingredient)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(3, $ref->count());
		$this->assertTrue($ref->first() instanceof Food);
		$this->assertEquals(1, $ref->first()->id);
		$this->assertTrue($ref->last() instanceof Food);
		$this->assertEquals(3, $ref->last()->id);
	}
}