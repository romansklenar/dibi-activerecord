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
		$this->assertEquals('officeCode', $without->referringAttribute);

		$this->assertEquals('Employee', $with->local);
		$this->assertEquals('Office', $with->referenced);
		$this->assertEquals('officeCode', $with->referringAttribute);



		$without = new BelongsToAssociation('Employee', 'Manager'); // Employee @belongsTo(Manager)
		$with    = new BelongsToAssociation('Employee', 'Manager', 'reportsTo'); // Employee @belongsTo(reportsTo => Manager)

		$this->assertEquals('Employee', $without->local);
		$this->assertEquals('Manager', $without->referenced);
		$this->assertEquals('employeeNumber', $without->referringAttribute);

		$this->assertEquals('Employee', $with->local);
		$this->assertEquals('Manager', $with->referenced);
		$this->assertEquals('reportsTo', $with->referringAttribute);



		$without = new BelongsToAssociation('Student', 'Supervisor'); // Student @belongsTo(Supervisor)
		$with    = new BelongsToAssociation('Student', 'Supervisor', 'reportsTo'); // Student @belongsTo(reportsTo => Supervisor)

		$this->assertEquals('Student', $without->local);
		$this->assertEquals('Supervisor', $without->referenced);
		$this->assertEquals('supervisorId', $without->referringAttribute);

		$this->assertEquals('Student', $with->local);
		$this->assertEquals('Supervisor', $with->referenced);
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



		$assignment = Assignment::find(2);
		$asc = new BelongsToAssociation('Assignment', 'Student');
		$ref = $asc->retreiveReferenced($assignment);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Student);
		$this->assertEquals(2, $ref->id);

		// referenced by attribute
		$student = Student::find(1);
		$asc = new BelongsToAssociation('Student', 'Supervisor', 'reportsTo');
		$ref = $asc->retreiveReferenced($student);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Supervisor);
		$this->assertEquals(3, $ref->id);



		Inflector::$railsStyle = TRUE;

		$car = Car::find(1);
		$asc = new BelongsToAssociation('Car', 'Guest');
		$ref = $asc->retreiveReferenced($car);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Guest);
		$this->assertEquals(1, $ref->id);

		// referenced by attribute
		$guest = Guest::find(1);
		$asc = new BelongsToAssociation('Guest', 'Guide', 'belongs_to');
		$ref = $asc->retreiveReferenced($guest);
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof Guide);
		$this->assertEquals(1, $ref->id);
	}
}