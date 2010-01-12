<?php

require_once __DIR__ . '/BirtBaseTestCase.php';

/**
 * Test class for Office.
 */
class OfficeTest extends BirtBaseTestCase {

	/** @var Office */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Office;
	}

	public function testGetTableName() {
		$this->assertEquals('Offices', $this->record->tableName);
	}

	public function testGetPrimaryName() {
		$this->assertEquals('officeCode', $this->record->primaryName);
	}

	public function testGetForeign() {
		$this->assertEquals('officeCode', $this->record->foreign);
	}

	public function testGetColumnNames() {
		$cols = array(
			"officeCode", "city", "phone", "addressLine1", "addressLine2",
			"state", "country", "postalCode", "territory", "position",
		);
		$this->assertEquals($cols, $this->record->columnNames);
	}

	public function testGetTypes() {
		$types = array(
			"officeCode" => dibi::TEXT,
			"city" => dibi::TEXT,
			"phone" => dibi::TEXT,
			"addressLine1" => dibi::TEXT,
			"addressLine2" => dibi::TEXT,
			"state" => dibi::TEXT,
			"country" => dibi::TEXT,
			"postalCode" => dibi::TEXT,
			"territory" => dibi::TEXT,
			"position" => dibi::INTEGER,
		);
		$this->assertEquals($types, $this->record->types);
	}

	public function testGetAssotiations() {
		$asc = $this->record->assotiations;
		
		$this->assertType('array', $asc);
		$this->assertEquals(1, count($asc));
		$this->assertTrue(isset($asc[Association::HAS_MANY]));
		$this->assertType('array', $asc[Association::HAS_MANY]);
		$this->assertEquals(1, count($asc[Association::HAS_MANY]));

		$a = $asc[Association::HAS_MANY][0];
		$this->assertTrue($a instanceof HasManyAssociation);
		$this->assertEquals('Office', $a->local);
		$this->assertEquals('Employee', $a->referenced);
		$this->assertEquals(NULL, $a->through);
	}

	public function testRelationEmployees() {
		ActiveRecordCollection::$loadImmediately = TRUE;
		$office = Office::find(1);
		$this->assertTrue($office->employees instanceof ActiveRecordCollection);
		$this->assertEquals(6, count($office->employees));
		$this->assertTrue(($employee = $office->employees->first()) instanceof Employee);
		$this->assertEquals(1002, $employee->employeeNumber);
		$this->assertTrue(($employee = $office->employees->last()) instanceof Employee);
		$this->assertEquals(1166, $employee->employeeNumber);
	}
	
}