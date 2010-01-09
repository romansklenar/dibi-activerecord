<?php

require_once __DIR__ . '/../ActiveRecordDatabaseTestCase.php';

/**
 * Test class for BelongsToAssociation.
 */
class BelongsToAssociationTest extends ActiveRecordDatabaseTestCase {

	public function testConstruct() {
		$without = new BelongsToAssociation('Employee', 'Office'); // Employee @belongsTo(Office)
		$with    = new BelongsToAssociation('Employee', 'Office', 'officeCode'); // Employee @belongsTo(officeCode => Office)

		$this->assertEquals('Employee', $without->local);
		$this->assertEquals('Office', $without->referenced);
		$this->assertEquals(NULL, $without->referringAttribute);

		$this->assertEquals('Employee', $with->local);
		$this->assertEquals('Office', $with->referenced);
		$this->assertEquals('officeCode', $with->referringAttribute);



		$without = new BelongsToAssociation('Employee', 'Manager'); // Employee @belongsTo(Manager)
		$with    = new BelongsToAssociation('Employee', 'Manager', 'reportsTo'); // Employee @belongsTo(reportsTo => Manager)

		$this->assertEquals('Employee', $without->local);
		$this->assertEquals('Manager', $without->referenced);
		$this->assertEquals(NULL, $without->referringAttribute);

		$this->assertEquals('Employee', $with->local);
		$this->assertEquals('Manager', $with->referenced);
		$this->assertEquals('reportsTo', $with->referringAttribute);
	}

	public function testIsInRelation() {
		$asc = new BelongsToAssociation('Employee', 'Office');
		$this->assertFalse($asc->isInRelation('Employee'));
		$this->assertTrue($asc->isInRelation('Office'));

		$asc = new BelongsToAssociation('Employee', 'Office', 'officeCode');
		$this->assertTrue($asc->isInRelation('Office'));
		$this->assertFalse($asc->isInRelation('Employee'));

		$asc = new BelongsToAssociation('Employee', 'Manager');
		$this->assertTrue($asc->isInRelation('Manager'));
		$this->assertFalse($asc->isInRelation('Employee'));

		$asc = new BelongsToAssociation('Employee', 'Manager', 'reportsTo');
		$this->assertTrue($asc->isInRelation('Manager'));
		$this->assertFalse($asc->isInRelation('Employee'));
	}

	public function testRetreiveReferenced() {
		$office = Office::find(1);
		$employee = Employee::find(1056);
		$manager = Manager::find(1002);
		

		$asc = new BelongsToAssociation('Employee', 'Office');
		$ref = $asc->retreiveReferenced($employee);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Office);
		$this->assertEquals(1, $ref->officeCode);
		

		$asc = new BelongsToAssociation('Employee', 'Office', 'officeCode');
		$ref = $asc->retreiveReferenced($employee);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Office);
		$this->assertEquals(1, $ref->officeCode);


		// self reference test
		$asc = new BelongsToAssociation('Employee', 'Manager', 'reportsTo');
		$ref = $asc->retreiveReferenced($employee);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Manager);
		$this->assertEquals(1002, $ref->employeeNumber);
	}
}