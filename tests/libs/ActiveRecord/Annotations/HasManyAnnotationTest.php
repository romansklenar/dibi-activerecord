<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for HasManyAnnotation.
 */
class HasManyAnnotationTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$rc = new ClassReflection('TestHasManyAnnotatedClass');
		$ann = $rc->annotations[Association::HAS_MANY];

		$this->assertType('array', $ann);
		$this->assertEquals(4, count($ann));
		$this->assertType('HasManyAnnotation', $ann[0]);
		$this->assertType('HasManyAnnotation', $ann[1]);
		$this->assertType('HasManyAnnotation', $ann[2]);
		$this->assertType('HasManyAnnotation', $ann[3]);

		$this->assertEquals(array('Employees'), $ann[0]->values);
		$this->assertEquals(array('Orders', 'Invoices'), $ann[1]->values);
		$this->assertEquals(array('OrderDetails' => 'Products'), $ann[2]->values);
		$this->assertEquals(array('Orders', 'OrderDetails' => 'Products', 'Invoices'), $ann[3]->values);
	}
}



/**
 * @hasMany(Employees)
 * @hasMany(Orders, Invoices)
 * @hasMany(through:OrderDetails => Products)
 * @hasMany(Orders, through:OrderDetails => Products, Invoices)
 */
class TestHasManyAnnotatedClass {}