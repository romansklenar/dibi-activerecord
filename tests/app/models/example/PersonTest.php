<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Test class for Person.
 */
class PersonTest extends BaseTestCase {

	/** @var Person */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Person();
	}

	public function testGetTableName() {
		$this->assertEquals('People', $this->record->tableName);
	}

	public function testGetPrimaryName() {
		$this->assertEquals('id', $this->record->primaryName);
	}

}