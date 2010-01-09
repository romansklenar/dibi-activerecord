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
		$this->assertTrue($ann[0] instanceof BelongsToAnnotation);
		$this->assertTrue($ann[1] instanceof BelongsToAnnotation);
		$this->assertTrue($ann[2] instanceof BelongsToAnnotation);

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