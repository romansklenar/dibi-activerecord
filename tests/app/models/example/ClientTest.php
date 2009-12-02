<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Test class for Client.
 */
class ClientTest extends BaseTestCase {

	/** @var Client */
	public $record;


	public function setUp() {
		parent::setUp();
		$this->record = new Client;
	}

	public function testGetTableName() {
		$this->assertEquals('Companies', $this->record->tableName);
	}

	public function testGetPrimaryName() {
		$this->assertEquals('id', $this->record->primaryName);
	}

	public function testGetColumnNames() {
		$cols = array('id', 'clientOf', 'isFirm', 'name');
		$this->assertEquals($cols, $this->record->columnNames);
	}

	public function testGetTypes() {
		$cols = array('id' => dibi::INTEGER, 'clientOf' => dibi::INTEGER, 'isFirm' => dibi::BOOL, 'name' => dibi::TEXT);
		$this->assertEquals($cols, $this->record->types);
	}

	public function testGetAssotiations() {
		$this->markTestSkipped();
		$this->assertEquals(array('Milestones', new ArrayObject(array('ProjectManager', 'bossId' => '> Author'))), Annotations::getAll(new ReflectionClass('C'), 'hasOne', TRUE));
		$asoc = new Association;
		$this->assertEquals($asoc, $this->record->assotiations);
	}
	
}