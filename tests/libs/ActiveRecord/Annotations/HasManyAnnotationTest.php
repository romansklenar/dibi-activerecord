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
		$this->assertTrue($ann[0] instanceof HasManyAnnotation);
		$this->assertTrue($ann[1] instanceof HasManyAnnotation);
		$this->assertTrue($ann[2] instanceof HasManyAnnotation);
		$this->assertTrue($ann[3] instanceof HasManyAnnotation);

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