<?php

require_once __DIR__ . '/BirtBaseTestCase.php';

/**
 * Test class for Employee.
 */
class EmployeeTest extends BirtBaseTestCase {

	/** @var Employee */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Employee;
	}


	public function testGetAssociations() {
		$asc = $this->record->associations;
		$this->assertType('array', $asc);
		$this->assertEquals(2, count($asc));

		$this->assertTrue(isset($asc[Association::HAS_MANY]));
		$this->assertType('array', $asc[Association::HAS_MANY]);
		$this->assertEquals(1, count($asc[Association::HAS_MANY]));

		$a = $asc[Association::HAS_MANY][0];
		$this->assertType('HasManyAssociation', $a);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Customer', $a->referenced);
		$this->assertEquals(NULL, $a->through);


		$this->assertTrue(isset($asc[Association::BELONGS_TO]));
		$this->assertType('array', $asc[Association::BELONGS_TO]);
		$this->assertEquals(2, count($asc[Association::BELONGS_TO]));

		$a = $asc[Association::BELONGS_TO][0];
		$this->assertType('BelongsToAssociation', $a);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Office', $a->referenced);
		$this->assertEquals('officeCode', $a->referringAttribute);
		
		$a = $asc[Association::BELONGS_TO][1];
		$this->assertType('BelongsToAssociation', $a);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Manager', $a->referenced);
		$this->assertEquals('reportsTo', $a->referringAttribute);
	}

	public function testRelationOffice() {
		$employee = Employee::find(1056);
		$this->assertType('Office', $employee->office);
		$this->assertEquals(1, $employee->office->officeCode);
	}

	public function testRelationCustomers() {
		$employee = Employee::find(1370);
		$this->assertType('ActiveRecordCollection', $employee->customers);
		$this->assertEquals(7, count($employee->customers));
		$this->assertType('Customer', $customer = $employee->customers->first());
		$this->assertEquals(103, $customer->customerNumber);
		$this->assertType('Customer', $customer = $employee->customers->last());
		$this->assertEquals(256, $customer->customerNumber);
	}

	public function testRelationManager() {
		// self reference
		$employee = Employee::find(1056);
		$this->assertType('Manager', $employee->manager);
		$this->assertEquals(1002, $employee->manager->employeeNumber);
	}
	
}