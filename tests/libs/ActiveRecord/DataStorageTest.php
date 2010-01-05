<?php

require_once 'PHPUnit/Framework.php';


/**
 * Test class for DataStorage.

	CREATE TABLE IF NOT EXISTS [Users] (
		[id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE CHECK ([id] > 0),
		[login] VARCHAR(64)  NOT NULL UNIQUE,
		[email] VARCHAR(128)  NOT NULL UNIQUE,
		[firstname] VARCHAR(64)  NOT NULL,
		[lastname] VARCHAR(64)  NOT NULL,
		[credit] INTEGER  DEFAULT 0 NOT NULL
	)
 
 */
class DataStorageTest extends PHPUnit_Framework_TestCase {

	/** @var array  test input data */
	private $original = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
	);

	/** @var array  test input data */
	private $modified = array(
			'email' => 'john.moe@example.com',
			'firstname' => 'John',
			'lastname' => 'Moe',
			'credit' => 100,
	);

	/** @var DataStorage */
	protected $object;


	public function setUp() {
		$this->object = new DataStorage($this->original, $this->modified);
	}

	public function tearDown() {}


	public function testConstructWithInvalidState() {
		$this->object = new DataStorage($this->original, $this->modified);
		$this->assertType('array', $this->object->original);
		$this->assertType('array', $this->object->modified);
	}

	public function testPropertyIsset() {
		$storage = $this->object;

		$this->assertTrue(isset($storage->id));
		$this->assertTrue(isset($storage->login));
		$this->assertTrue(isset($storage->email));
		$this->assertTrue(isset($storage->firstname));
		$this->assertTrue(isset($storage->lastname));
		$this->assertTrue(isset($storage->credit));
		$this->assertFalse(isset($storage->undeclared));
	}

	public function testOffsetExists() {
		$storage = $this->object;

		$this->assertTrue(isset($storage['id']));
		$this->assertTrue(isset($storage['login']));
		$this->assertTrue(isset($storage['email']));
		$this->assertTrue(isset($storage['firstname']));
		$this->assertTrue(isset($storage['lastname']));
		$this->assertTrue(isset($storage['credit']));
		$this->assertFalse(isset($storage['undeclared']));
	}

	public function testPropertyGet() {

		$storage = $this->object;
		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.moe@example.com',
			'firstname' => 'John',
			'lastname' => 'Moe',
			'credit' => 100,
		);

		$this->assertEquals($cmp['id'], $storage->id);
		$this->assertEquals($cmp['login'], $storage->login);
		$this->assertEquals($cmp['email'], $storage->email);
		$this->assertEquals($cmp['firstname'], $storage->firstname);
		$this->assertEquals($cmp['lastname'], $storage->lastname);
		$this->assertEquals($cmp['credit'], $storage->credit);
	}

	public function testOffsetGet() {

		$storage = $this->object;
		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.moe@example.com',
			'firstname' => 'John',
			'lastname' => 'Moe',
			'credit' => 100,
		);

		$this->assertEquals($cmp['id'], $storage['id']);
		$this->assertEquals($cmp['login'], $storage['login']);
		$this->assertEquals($cmp['email'], $storage['email']);
		$this->assertEquals($cmp['firstname'], $storage['firstname']);
		$this->assertEquals($cmp['lastname'], $storage['lastname']);
		$this->assertEquals($cmp['credit'], $storage['credit']);
	}

	public function testPropertySet() {
		$storage = $this->object;
		
		$storage->id = 5;
		$this->assertEquals(5, $storage->id);

		$this->setExpectedException('MemberAccessException');
		$storage->unknown = 20;
	}

	public function testOffsetSet() {
		$storage = $this->object;

		$storage['id'] = 15;
		$this->assertEquals(15, $storage['id']);

		$this->setExpectedException('MemberAccessException');
		$storage['unknown'] = 30;
	}

	public function testPropertyUnset() {
		$storage = $this->object;

		$this->setExpectedException('NotSupportedException');
		unset($storage->id);
	}
	
	public function testOffsetUnset() {
		$storage = $this->object;
		
		$this->setExpectedException('NotSupportedException');
		unset($storage['id']);
	}

}
