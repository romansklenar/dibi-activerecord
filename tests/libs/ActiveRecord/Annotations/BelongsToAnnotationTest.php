<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for BelongsToAnnotation.
 */
class BelongsToAnnotationTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$rc = new ClassReflection('TestBelongsToAnnotatedClass');
		$ann = $rc->annotations[Association::BELONGS_TO];

		$this->assertType('array', $ann);
		$this->assertEquals(3, count($ann));
		$this->assertType('BelongsToAnnotation', $ann[0]);
		$this->assertType('BelongsToAnnotation', $ann[1]);
		$this->assertType('BelongsToAnnotation', $ann[2]);

		$this->assertEquals(array('Employee'), $ann[0]->values);
		$this->assertEquals(array('clientOf' => 'Firm'), $ann[1]->values);
		$this->assertEquals(array('Clients', 'clientOf' => 'Firm',  'Invoices'), $ann[2]->values);
	}
}



/**
 * @belongsTo(Employee)
 * @belongsTo(clientOf => Firm)
 * @belongsTo(Clients, clientOf => Firm, Invoices)
 */
class TestBelongsToAnnotatedClass {}