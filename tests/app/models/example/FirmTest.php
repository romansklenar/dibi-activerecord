<?php

require_once __DIR__ . '/ExampleBaseTestCase.php';

/**
 * Test class for Firm.
 */
class FirmTest extends ExampleBaseTestCase {

	/** @var Firm */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Firm;
	}

	public function testGetTableName() {
		$this->assertEquals('Companies', $this->record->tableName);
	}

	public function testGetPrimaryKey() {
		$this->assertEquals('id', $this->record->primaryKey);
	}

}