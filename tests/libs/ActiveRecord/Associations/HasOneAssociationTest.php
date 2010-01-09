<?php

require_once __DIR__ . '/../ActiveRecordDatabaseTestCase.php';

/**
 * Test class for HasOneAssociation.
 */
class HasOneAssociationTest extends ActiveRecordDatabaseTestCase {
	
	public function testConstruct() {
		$association = new HasOneAssociation('Office', 'Employee'); // Office @hasOne(Employee)
		$this->assertEquals('Office', $association->local);
		$this->assertEquals('Employee', $association->referenced);
	}

	public function testIsInRelation() {
		$asc = new HasOneAssociation('Office', 'Employee');
		$this->assertTrue($asc->isInRelation('Employee'));
		$this->assertFalse($asc->isInRelation('Employees'));
		$this->assertFalse($asc->isInRelation('Office'));
		$this->assertFalse($asc->isInRelation('Manager'));
		$this->assertFalse($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));
	}

	public function testRetreiveReferenced() {
		$office = Office::find(1);

		$asc = new HasOneAssociation('Office', 'Employee');
		$ref = $asc->retreiveReferenced($office);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Employee);
		$this->assertEquals(1002, $ref->employeeNumber);
	}
}