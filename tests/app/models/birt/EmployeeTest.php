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


	public function testGetAssotiations() {
		$asc = $this->record->assotiations;
		$this->assertType('array', $asc);
		$this->assertEquals(2, count($asc));

		$this->assertTrue(isset($asc[Association::HAS_MANY]));
		$this->assertType('array', $asc[Association::HAS_MANY]);
		$this->assertEquals(1, count($asc[Association::HAS_MANY]));

		$a = $asc[Association::HAS_MANY][0];
		$this->assertTrue($a instanceof HasManyAssociation);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Customer', $a->referenced);
		$this->assertEquals(NULL, $a->through);


		$this->assertTrue(isset($asc[Association::BELONGS_TO]));
		$this->assertType('array', $asc[Association::BELONGS_TO]);
		$this->assertEquals(2, count($asc[Association::BELONGS_TO]));

		$a = $asc[Association::BELONGS_TO][0];
		$this->assertTrue($a instanceof BelongsToAssociation);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Office', $a->referenced);
		$this->assertEquals(NULL, $a->referringAttribute);
		
		$a = $asc[Association::BELONGS_TO][1];
		$this->assertTrue($a instanceof BelongsToAssociation);
		$this->assertEquals('Employee', $a->local);
		$this->assertEquals('Manager', $a->referenced);
		$this->assertEquals('reportsTo', $a->referringAttribute);
	}

	public function testGetOffice() {
		$employee = Employee::find(1056);
		$this->assertTrue($employee->office instanceof Office);
		$this->assertEquals(1, $employee->office->officeCode);
	}

	public function _testGetManager() {
		// self reference
		$employee = Employee::find(1056);
		$this->assertTrue($employee->manager instanceof Manager);
		$this->assertEquals(1002, $employee->manager->employeeNumber);
	}
	
}