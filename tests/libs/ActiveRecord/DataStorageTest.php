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
	private $input = array(
		'login' => 'john007',
		'email' => 'john.doe@example.com',
		'lastname' => 'Doe',
	);

	/** @var array  test input data */
	private $defaults = array(
		'credit' => 0,
	);

	/** @var array  available storage fields */
	private $fields = array(
		'id', 'login', 'email', 'firstname', 'lastname', 'credit'
	);

	/** @var DataStorage */
	protected $object;


	public function setUp() {
		$this->object = new DataStorage($this->fields, $this->input, $this->defaults, DataStorage::STATE_NEW);
	}

	public function tearDown() {}


	public function testConstructWithInvalidState() {
		$this->setExpectedException('InvalidArgumentException');
		$this->object = new DataStorage($this->fields, $this->input, $this->defaults, '*** invalid ***');
	}
	

	public function testAccess() {
		$storage = $this->object;
		
		$this->assertTrue(isset($storage->id));
		$this->assertTrue(isset($storage->login));
		$this->assertTrue(isset($storage->email));
		$this->assertTrue(isset($storage->firstname));
		$this->assertTrue(isset($storage->lastname));
		$this->assertTrue(isset($storage->credit));

		$this->assertTrue(isset($storage['id']));
		$this->assertTrue(isset($storage['login']));
		$this->assertTrue(isset($storage['email']));
		$this->assertTrue(isset($storage['firstname']));
		$this->assertTrue(isset($storage['lastname']));
		$this->assertTrue(isset($storage['credit']));

		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$this->assertEquals($cmp['id'], $storage->id);
		$this->assertEquals($cmp['login'], $storage->login);
		$this->assertEquals($cmp['email'], $storage->email);
		$this->assertEquals($cmp['firstname'], $storage->firstname);
		$this->assertEquals($cmp['lastname'], $storage->lastname);
		$this->assertEquals($cmp['credit'], $storage->credit);

		$this->assertEquals($cmp['id'], $storage['id']);
		$this->assertEquals($cmp['login'], $storage['login']);
		$this->assertEquals($cmp['email'], $storage['email']);
		$this->assertEquals($cmp['firstname'], $storage['firstname']);
		$this->assertEquals($cmp['lastname'], $storage['lastname']);
		$this->assertEquals($cmp['credit'], $storage['credit']);

		$storage->id = 5;
		$this->assertEquals(5, $storage->id);

		$storage['id'] = 15;
		$this->assertEquals(15, $storage['id']);

		$this->setExpectedException('NotSupportedException');
		unset($storage['id']);
	}
	

	public function testNewRecord() {
		$this->object = $storage = new DataStorage($this->fields, $this->input, $this->defaults, DataStorage::STATE_NEW);

		$bkp = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$this->assertEquals(DataStorage::STATE_NEW, $storage->state);
		$this->assertEquals($bkp, $storage->values);
		$this->assertEquals($bkp, $storage->modified);
		$this->assertEquals($storage->values, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$this->assertEquals($storage->values, $storage->modified);
		$this->assertEquals($storage->values, $storage->backup);
		$this->assertEquals($storage->backup, $storage->modified);

		$storage->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals($cmp, $storage->modified);
		$this->assertEquals($storage->values, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->discard();
		$this->assertEquals(DataStorage::STATE_NEW, $storage->state);
		$this->assertEquals($bkp, $storage->values);
		$this->assertEquals($bkp, $storage->modified);
		$this->assertEquals($storage->values, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals($cmp, $storage->modified);
		$this->assertEquals($storage->values, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->save();
		$this->assertEquals(DataStorage::STATE_EXISTING, $storage->state);
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array(), $storage->modified);
		$this->assertEquals($cmp, $storage->backup);
		$this->assertEquals($storage->values, $storage->backup);
	}

	public function testExistingRecord() {
		$this->object = $storage = new DataStorage($this->fields, $this->input, $this->defaults, DataStorage::STATE_EXISTING);

		$bkp = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$this->assertEquals(DataStorage::STATE_EXISTING, $storage->state);
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array(), $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array('credit' => 5), $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->firstname = 'John';
		$cmp['firstname'] = 'John';
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array('credit' => 5, 'firstname' => 'John'), $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->save();
		$this->assertEquals(DataStorage::STATE_EXISTING, $storage->state);
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array(), $storage->modified);
		$this->assertEquals($cmp, $storage->backup);
		$this->assertEquals($storage->values, $storage->backup);
	}


	public function testBlankRecord() {
		// allmost same as new record test
		$this->object = $storage = new DataStorage($this->fields, array(), $this->defaults, DataStorage::STATE_NEW);

		$bkp = $cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
		);

		$this->assertEquals(DataStorage::STATE_NEW, $storage->state);
		$this->assertEquals($bkp, $storage->values);
		$this->assertEquals($bkp, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals($cmp, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$storage->discard();
		$this->assertEquals($bkp, $storage->values);
		$this->assertEquals($bkp, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);
		$this->assertEquals($storage->values, $storage->backup);
		$this->assertEquals(DataStorage::STATE_NEW, $storage->state);
	}


	public function testNewRecordWithNonExistingAtributes() {
		$input = $this->data + array('non-existing-column' => '****');
		$values = $input + $this->defaults;
		foreach ($this->fields as $field)
			$result[$field] = isset($values[$field]) ? $values[$field] : NULL;
		$this->assertFalse(isset($result["non-existing-column"]));

		// allmost same as new record test
		$this->object = $storage = new DataStorage($this->fields, $input, $this->defaults, DataStorage::STATE_NEW);

		$bkp = $cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
		);

		$this->assertEquals(DataStorage::STATE_NEW, $storage->state);
		$this->assertEquals($bkp, $storage->values);
		$this->assertEquals($bkp, $storage->modified);
		$this->assertEquals($bkp, $storage->backup);
	}


	public function testPrimaryUpdate() {
		$this->object = $storage = new DataStorage($this->fields, array('id' => 1) + $this->input, $this->defaults, DataStorage::STATE_EXISTING);

		$bkp = $cmp = array(
			'id' => 1,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$this->assertEquals(DataStorage::STATE_EXISTING, $storage->state);
		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals(array(), $storage->modified);
		$this->assertEquals($bkp, $storage->backup);

		$this->assertEquals(1, $storage->id);
		$this->assertEquals(1, $storage->backup['id']);
		$this->assertTrue(empty($storage->modified));

		$storage->id = 2;

		$this->assertEquals(2, $storage->id);
		$this->assertEquals(1, $storage->backup['id']);
		$this->assertEquals(2, $storage->modified['id']);

		$storage->save();

		$this->assertEquals(2, $storage->id);
		$this->assertEquals(2, $storage->backup['id']);
		$this->assertTrue(empty($storage->modified));

		$storage->id = 3;

		$this->assertEquals(3, $storage->id);
		$this->assertEquals(2, $storage->backup['id']);
		$this->assertFalse(empty($storage->modified));
		$this->assertEquals(3, $storage->modified['id']);

		$storage->save();

		$this->assertEquals(3, $storage->id);
		$this->assertEquals(3, $storage->backup['id']);
		$this->assertTrue(empty($storage->modified));

		$storage->id = 3;

		$this->assertEquals(3, $storage->id);
		$this->assertEquals(3, $storage->backup['id']);
		$this->assertTrue(empty($storage->modified));

		// nothing changed, update will not be executed
		$storage->save();

		$storage->id = 4;

		$this->assertEquals(4, $storage->id);
		$this->assertEquals(3, $storage->backup['id']);
		$this->assertFalse(empty($storage->modified));
		$this->assertEquals(4, $storage->modified['id']);

		$storage->discard();

		$this->assertEquals(3, $storage->id);
		$this->assertEquals(3, $storage->backup['id']);
		$this->assertTrue(empty($storage->modified));
	}


	public function testClear() {
		$this->object = $storage = new DataStorage($this->fields, array(), $this->defaults, DataStorage::STATE_EXISTING);
		$storage->clear();

		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => NULL,
		);

		$this->assertEquals($cmp, $storage->values);
		$this->assertEquals($cmp, $storage->backup);
		$this->assertEquals(array(), $storage->modified);
		$this->assertTrue($storage->isFrozen());

		$this->setExpectedException('InvalidStateException');
		$storage->id = 123;
	}

}
