<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for HasOneAnnotation.
 */
class HasOneAnnotationTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$rc = new ClassReflection('TestHasOneAnnotatedClass');
		$ann = $rc->annotations[Association::HAS_ONE];

		$this->assertType('array', $ann);
		$this->assertEquals(2, count($ann));
		$this->assertType('HasOneAnnotation', $ann[0]);
		$this->assertType('HasOneAnnotation', $ann[1]);

		$this->assertEquals(array('Employees'), $ann[0]->values);
		$this->assertEquals(array('Orders', 'Invoices'), $ann[1]->values);
	}
}



/**
 * @hasOne(Employees)
 * @hasOne(Orders, Invoices)
 */
class TestHasOneAnnotatedClass {}