<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for HasAndBelongsToManyAnnotation.
 */
class HasAndBelongsToManyAnnotationTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$rc = new ClassReflection('TestHasAndBelongsToManyAnnotatedClass');
		$ann = $rc->annotations[Association::HAS_AND_BELONGS_TO_MANY];
		
		$this->assertType('array', $ann);
		$this->assertEquals(3, count($ann));
		$this->assertType('HasAndBelongsToManyAnnotation', $ann[0]);
		$this->assertType('HasAndBelongsToManyAnnotation', $ann[1]);
		$this->assertType('HasAndBelongsToManyAnnotation', $ann[2]);

		$this->assertEquals(array('Invoices'), $ann[0]->values);
		$this->assertEquals(array('ClientsInvoices' => 'Clients'), $ann[1]->values);
		$this->assertEquals(array('Invoices', 'ClientsInvoices' => 'Clients', 'Employees'), $ann[2]->values);
	}
}


/**
 * @hasAndBelongsToMany(Invoices)
 * @hasAndBelongsToMany(joinTable:ClientsInvoices => Clients)
 * @hasAndBelongsToMany(Invoices, joinTable:ClientsInvoices => Clients, Employees)
 */
class TestHasAndBelongsToManyAnnotatedClass {}