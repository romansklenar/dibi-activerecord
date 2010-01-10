<?php

require_once __DIR__ . '/ExampleBaseTestCase.php';

/**
 * Test class for Person.
 */
class PersonTest extends ExampleBaseTestCase {

	/** @var Person */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Person;
	}

	public function testGetTableName() {
		$this->assertEquals('People', $this->record->tableName);
	}

	public function testGetPrimaryName() {
		$this->assertEquals('id', $this->record->primaryName);
	}

}