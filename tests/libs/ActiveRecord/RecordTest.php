<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for Record.
 */
class RecordTest extends PHPUnit_Framework_TestCase {

	/** @var Record */
	protected $object;

	/** @var array  test input data */
	private $values = array(
		'login' => 'john007',
		'email' => 'john.doe@example.com',
		'lastname' => 'Doe',
	);


	protected function setUp() {
		$this->object = new MockRecord($this->values);
	}

	protected function tearDown() {}
	

	public function testConstruct() {
		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
		);

		$this->object = new MockRecord(new ArrayObject($this->values));
		$this->assertType('array', $this->object->values);
		$this->assertEquals(6, count($this->object->values));
		$this->assertEquals(0, $this->object->credit); // default value should be known
		$this->assertEquals($cmp, $this->object->values);

		
		$this->object = new MockRecord($this->values);
		$this->assertType('array', $this->object->values);
		$this->assertEquals(6, count($this->object->values));
		$this->assertEquals($cmp, $this->object->values);

		$this->setExpectedException('InvalidArgumentException');
		$this->object = new MockRecord("*** not compatible ***");
	}

	public function testOffsetGet() {
		$this->assertEquals(NULL, $this->object['id']);
		$this->assertEquals(NULL, $this->object['firstname']);
		$this->assertEquals('john.doe@example.com', $this->object['email']);
		$this->assertEquals('DOE', $this->object['lastname']);
		
		$this->setExpectedException('MemberAccessException');
		$this->object['undeclared'];
	}

	public function testPropertyGet() {
		$this->assertEquals(NULL, $this->object->id);
		$this->assertEquals(NULL, $this->object->firstname);
		$this->assertEquals('john.doe@example.com', $this->object->email);
		$this->assertEquals('DOE', $this->object->lastname);

		$this->setExpectedException('MemberAccessException');
		$this->object->undeclared;
	}

	public function testOffsetSet() {
		$this->object['id'] = 2;
		$this->object['email'] = 'jack.doe@example.com';
		$this->object['firstname'] = 'Jack';
		
		$this->assertEquals(2, $this->object['id']);
		$this->assertEquals('jack.doe@example.com', $this->object['email']);
		$this->assertEquals('Jack', $this->object['firstname']);
	}

	public function testPropertySet() {
		$this->object->id = 3;
		$this->object->email = 'jane.doe@example.com';
		$this->object->firstname = 'Jane';

		$this->assertEquals(3, $this->object->id);
		$this->assertEquals('jane.doe@example.com', $this->object->email);
		$this->assertEquals('Jane', $this->object->firstname);

		$this->setExpectedException('MemberAccessException');
		$this->object->undeclared = '***';
	}

	public function testOffsetExists() {
		$this->assertTrue(isset($this->object['id']));
		$this->assertTrue(isset($this->object['email']));
		$this->assertTrue(isset($this->object['firstname']));
		$this->assertFalse(isset($this->object['undeclared']));
	}
	
	public function testPropertyExists() {
		$this->assertTrue(isset($this->object->id));
		$this->assertTrue(isset($this->object->email));
		$this->assertTrue(isset($this->object->firstname));
		$this->assertFalse(isset($this->object->undeclared));
	}

	public function testOffsetUnset() {
		$this->setExpectedException('MemberAccessException');
		unset($this->object['id']);
	}

	public function testPropertyUnset() {
		$this->setExpectedException('MemberAccessException');
		unset($this->object->id);
	}

	public function testGetColumnNames() {
		$cmp = array('id', 'login', 'email', 'firstname', 'lastname', 'credit');
		$this->assertEquals($cmp, $this->object->columnNames);
	}

	public function testGetDefaultValues() {
		$cmp = array('credit' => 0);
		$this->assertEquals($cmp, $this->object->defaultValues);
	}

	public function testGetModifiedValues() {
		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
		);

		$this->object = new MockRecord($this->values);
		$this->assertType('array', $this->object->values);
		$this->assertEquals(6, count($this->object->values));
		$this->assertEquals(0, $this->object->credit); // default value should be known
		$this->assertEquals($cmp, $this->object->values);

		$this->object->lastname = 'Moe';
		$cmp['lastname'] = 'MOE';
		$this->assertType('array', $this->object->modifiedValues);
		$this->assertEquals(6, count($this->object->modifiedValues));
		$this->assertEquals('MOE', $this->object->modifiedValues['lastname']); // default value should be known
		$this->assertEquals($cmp, $this->object->modifiedValues);

		$this->object->save();
		$this->object->lastname = 'Doe';
		$cmp['lastname'] = 'DOE';
		$this->assertType('array', $this->object->modifiedValues);
		$this->assertEquals(1, count($this->object->modifiedValues));
		$this->assertEquals('DOE', $this->object->modifiedValues['lastname']);
	}

	public function testGetGetStorage() {
		$storage = $this->object->storage;
		$this->assertTrue($storage instanceof DataStorage);
	}

	public function testGetState() {
		$values = array(
			'id' => NULL, // not defined value
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);
		
		$record = new MockRecord($values);
		// Record is simple (doesn't know anything about primary keys)
		$this->assertTrue($record->isRecordExisting());


		$values = array(
			'id' => 11,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$record = new MockRecord($values);
		$this->assertTrue($record->isRecordExisting());
		
		$values = array(
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'lastname' => 'Doe',
		);

		$record = new MockRecord($values);
		$this->assertTrue($record->isRecordNew());

		$record = new MockRecord(array());
		$this->assertTrue($record->isRecordNew());

		$record = new MockRecord();
		$this->assertTrue($record->isRecordNew());
		
		$record = new MockRecord($this->values, Record::STATE_EXISTING);
		$this->assertTrue($record->isRecordExisting());

		$record = new MockRecord($this->values, Record::STATE_NEW);
		$this->assertTrue($record->isRecordNew());

		$this->setExpectedException('InvalidArgumentException');
		$record = new MockRecord($this->values, '*** invalid ***');
	}

	public function testFreeze() {
		$record = new MockRecord($this->values);
		$record->freeze();

		$this->setExpectedException('InvalidStateException');
		$record->id = 123;
	}



	/********************* MockRecord additional methods *********************/



	public function testGetInitials() {
		$this->object->firstname = 'John';
		$this->assertEquals('J. D.', $this->object->getInitials());
		$this->assertEquals('J. D.', $this->object['initials']);
		$this->assertEquals('J. D.', $this->object->initials);
	}

	public function testSetInitials() {
		$this->setExpectedException('MemberAccessException');
		$this->object->initials = 'X. X.';

		$this->setExpectedException('MemberAccessException');
		$this->object['initials'] = 'X. X.';
	}

	public function testGetFullnameByFunction() {
		$this->object->firstname = 'John';
		$this->assertEquals('John DOE', $this->object->getFullname());
		$this->assertEquals('John DOE', $this->object['fullname']);
		$this->assertEquals('John DOE', $this->object->fullname);
	}

	public function testSetFullnameByFunction() {
		$this->object->setFullname('Jack Black');
		$this->assertEquals('Jack', $this->object->firstname);
		$this->assertEquals('BLACK', $this->object->lastname);
	}

	public function testSetFullnameByArrayAccess() {
		$this->object['fullname'] = 'Jack Black';
		$this->assertEquals('Jack', $this->object->firstname);
		$this->assertEquals('BLACK', $this->object->lastname);
	}
	
	public function testSetFullnameByProperty() {
		$this->object->fullname = 'Jack Black';
		$this->assertEquals('Jack', $this->object->firstname);
		$this->assertEquals('BLACK', $this->object->lastname);
	}
}



/**
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $firstname
 * @property string $lastname
 * @property int $credit
 * @property string $fullname
 * @property-read string $initials
 */
class MockRecord extends Record {
	
	/** @var array  internal default data storage*/
	protected $defaults = array(
		'credit' => 0,
	);

	/** @var array  internal column name storage */
	protected $columns = array(
		'id', 'login', 'email', 'firstname', 'lastname', 'credit'
	);


	/**
	 * @retrun array
	 */
	public function getColumnNames() {
		return parent::getColumnNames();
	}


	/**
	 * @retrun array
	 */
	public function getDefaultValues() {
		return parent::getDefaultValues();
	}

	
	/**
	 * @retrun DataStorage
	 */
	public function getStorage() {
		return parent::getStorage();
	}



	/********************* MockRecord custom getters & setters *********************/



	public function getFullname() {
		return $this->firstname . ' ' . $this->lastname;
	}

	public function setFullname($fullname) {
		$parts = explode(' ', $fullname);
		$this->firstname = $parts[0];
		$this->lastname = $parts[1];
	}

	/**
	 * MockRecord defined property getter
	 * @return string
	 */
	public function getInitials() {
		return String::upper(sprintf("%s. %s.", $this->firstname[0], $this->lastname[0]));
	}

	/**
	 * MockRecord defined property getter
	 * @return string
	 */
	public function getLastname() {
		return String::upper($this->storage['lastname']);
	}
}