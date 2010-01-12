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
		'registered' => '2010-01-01 12:00:00',
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
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);

		$this->object = new MockRecord(new ArrayObject($this->values));
		$this->assertType('array', $this->object->values);
		$this->assertEquals(7, count($this->object->values));
		$this->assertEquals(0, $this->object->credit); // default value should be known
		$this->assertEquals($cmp, $this->object->values);

		
		$this->object = new MockRecord($this->values);
		$this->assertType('array', $this->object->values);
		$this->assertEquals(7, count($this->object->values));
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

		$this->setExpectedException('MemberAccessException');
		$this->object['undeclared'] = '***';
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
		$this->setExpectedException('NotSupportedException');
		unset($this->object['id']);
	}

	public function testPropertyUnset() {
		$this->setExpectedException('NotSupportedException');
		unset($this->object->id);
	}

	public function testGetColumnNames() {
		$cmp = array('id', 'login', 'email', 'firstname', 'lastname', 'credit', 'registered');
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
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);

		$this->object = new MockRecord($this->values);
		$this->assertType('array', $this->object->values);
		$this->assertEquals(7, count($this->object->values));
		$this->assertEquals(0, $this->object->credit); // default value should be known
		$this->assertEquals($cmp, $this->object->values);

		$this->object->lastname = 'Moe';
		$cmp['lastname'] = 'MOE';
		$this->assertType('array', $this->object->modifiedValues);
		$this->assertEquals(7, count($this->object->modifiedValues));
		$this->assertEquals('MOE', $this->object->modifiedValues['lastname']); // default value should be known
		$this->assertEquals($cmp, $this->object->modifiedValues);

		$this->object->save();
		$this->object->lastname = 'Doe';
		$cmp['lastname'] = 'DOE';
		$this->assertType('array', $this->object->modifiedValues);
		$this->assertEquals(1, count($this->object->modifiedValues));
		$this->assertEquals('DOE', $this->object->modifiedValues['lastname']);
	}

	public function testGetStorage() {
		$storage = $this->object->storage;
		$this->assertType('DataStorage', $storage);
	}

	public function testState() {
		$values = array(
			'id' => NULL, // not defined value
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
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
			'registered' => new DateTime('2010-01-01 12:00:00'),
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



	/********************* Executors tests *********************/



	public function testIsClean() {
		$record = new MockRecord($this->values, MockRecord::STATE_NEW);
		$this->assertFalse($record->isClean());

		$record->credit = 5;
		$this->assertFalse($record->isClean());
		$record->discard();
		$this->assertFalse($record->isClean());


		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);
		$this->assertTrue($record->isClean());

		$record->credit = 5;
		$this->assertFalse($record->isClean());
		$record->discard();
		$this->assertTrue($record->isClean());
	}

	public function testIsDirty() {
		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);
		$this->assertFalse($record->isDirty());

		$record->credit = 5;
		$this->assertTrue($record->isDirty());
		$record->discard();
		$this->assertFalse($record->isDirty());


		$record = new MockRecord($this->values, MockRecord::STATE_NEW);
		$this->assertTrue($record->isDirty());

		$record->credit = 5;
		$this->assertTrue($record->isDirty());
		$record->discard();
		$this->assertTrue($record->isDirty());
	}

	public function testDestroy() {
		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->destroy();
		
		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => NULL,
			'registered' => NULL,
		);

		$this->assertTrue($record->isRecordDeleted());
		$this->assertTrue($record->isFrozen());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);



		$record = new MockRecord($this->values, MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->destroy();

		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => NULL,
			'registered' => NULL,
		);

		$this->assertTrue($record->isRecordDeleted());
		$this->assertTrue($record->isFrozen());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);
	}

	public function testSave() {
		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->credit = 5;
		$cmp['credit'] = 5;

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array('credit' => 5), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		
		$record->save();

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);



		$record = new MockRecord($this->values, MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->credit = 5;
		$cmp['credit'] = 5;

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->save();

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);
	}

	public function testDiscard() {
		$record = new MockRecord($this->values, MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->discard();
		$cmp['credit'] = 0;
		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);



		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array('credit' => 5), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->discard();
		$cmp['credit'] = 0;
		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
	}



	/********************* Scenario tests *********************/



	public function testNewRecord() {
		$record = new MockRecord($this->values, MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->discard();
		$cmp['credit'] = 0;
		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->modifiedValues);

		$record->save();
		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);
		$this->assertEquals($record->values, $record->originalValues);
	}

	public function testExistingRecord() {
		$record = new MockRecord($this->values, MockRecord::STATE_EXISTING);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array('credit' => 5), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->firstname = 'John';
		$cmp['firstname'] = 'John';
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array('credit' => 5, 'firstname' => 'John'), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->save();
		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($cmp, $record->originalValues);
		$this->assertEquals($record->values, $record->originalValues);
	}

	public function testBlankRecord() {
		// allmost same as new record test
		$record = new MockRecord(array(), MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
			'registered' => NULL,
		);

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($orig, $record->values);
		$this->assertEquals($orig, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->credit = 5;
		$cmp['credit'] = 5;
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals($cmp, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$record->discard();
		$this->assertEquals($orig, $record->values);
		$this->assertEquals($orig, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
		$this->assertEquals($record->values, $record->originalValues);
		$this->assertTrue($record->isRecordNew());
	}

	public function testNewRecordWithNonExistingAtributes() {
		// allmost same as new record test
		$record = new MockRecord(array('non-existing-column' => '****'), MockRecord::STATE_NEW);

		$orig = $cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
			'registered' => NULL,
		);

		$this->assertTrue($record->isRecordNew());
		$this->assertEquals($orig, $record->values);
		$this->assertEquals($orig, $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);
	}

	public function testPrimaryUpdate() {
		$record = new MockRecord($this->values + array('id' => 1), MockRecord::STATE_EXISTING);

		$orig = $cmp = array(
			'id' => 1,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'DOE',
			'credit' => 0,
			'registered' => new DateTime('2010-01-01 12:00:00'),
		);
		$orig['lastname'] = 'Doe';
		$orig['registered'] = '2010-01-01 12:00:00';

		$this->assertTrue($record->isRecordExisting());
		$this->assertEquals($cmp, $record->values);
		$this->assertEquals(array(), $record->modifiedValues);
		$this->assertEquals($orig, $record->originalValues);

		$this->assertEquals(1, $record->id);
		$this->assertEquals(1, $record->originalValues['id']);
		$this->assertTrue(empty($record->modifiedValues));

		$record->id = 2;

		$this->assertEquals(2, $record->id);
		$this->assertEquals(1, $record->originalValues['id']);
		$this->assertEquals(2, $record->modifiedValues['id']);

		$record->save();

		$this->assertEquals(2, $record->id);
		$this->assertEquals(2, $record->originalValues['id']);
		$this->assertTrue(empty($record->modifiedValues));

		$record->id = 3;

		$this->assertEquals(3, $record->id);
		$this->assertEquals(2, $record->originalValues['id']);
		$this->assertFalse(empty($record->modifiedValues));
		$this->assertEquals(3, $record->modifiedValues['id']);

		$record->save();

		$this->assertEquals(3, $record->id);
		$this->assertEquals(3, $record->originalValues['id']);
		$this->assertTrue(empty($record->modifiedValues));

		$record->id = 3;

		$this->assertEquals(3, $record->id);
		$this->assertEquals(3, $record->originalValues['id']);
		$this->assertTrue(empty($record->modifiedValues));

		// nothing changed, update will not be executed
		$record->save();

		$record->id = 4;

		$this->assertEquals(4, $record->id);
		$this->assertEquals(3, $record->originalValues['id']);
		$this->assertFalse(empty($record->modifiedValues));
		$this->assertEquals(4, $record->modifiedValues['id']);

		$record->discard();

		$this->assertEquals(3, $record->id);
		$this->assertEquals(3, $record->originalValues['id']);
		$this->assertTrue(empty($record->modifiedValues));
	}

	public function testConflictProperties() {
		$values = array(
			'state' => 'Czech Republic',
			'storage' => 'storage#1',
			'defaults' => 'default#1',
			'columns' => 'column#1',
		);

		$record = new ConflictRecord($values, MockRecord::STATE_EXISTING);
		$this->assertEquals($values, $record->values);

		$record = new ConflictRecord($values, MockRecord::STATE_NEW);
		$this->assertEquals($values, $record->values);
		$this->assertEquals($values, $record->modifiedValues);
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

	public function testIsEducated() {
		$this->assertTrue($this->object->isEducated());
		$this->assertTrue($this->object->educated);
		$this->assertTrue($this->object['educated']);
	}

	public function testCasting() {
		$this->assertType('DateTime', $this->object->registered);

		$this->markTestSkipped('TODO: Bug or feature?');
		// zpusobem jako je ukazano v metode getBypassedAttribute() se da pretypovani obejit
		// otazka je jestli to brat jako chtene ci nechtene chovani
		// v pripade ze nechtene, muselo by se pretypovani presunout primo do tridy DataStorage
		// ale spojecne i s datavymi typy/reference na Record, ktera by byla u kazde instance DataStorage,
		// coz by byla rezie navic
		$this->assertType('DateTime', $this->object->getBypassedAttribute('registered'));
	}
}


class ConflictRecord extends Record {

	protected $defaults = array();

	protected $columns = array('state', 'storage', 'defaults', 'columns');

	protected $types = array(
		'state' => dibi::TEXT,
		'storage' => dibi::TEXT,
		'defaults' => dibi::TEXT,
		'columns' => dibi::TEXT,
	);

	/**
	 * @return array
	 */
	public function getModifiedValues() {
		return parent::getModifiedValues();
	}
}


/**
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $firstname
 * @property string $lastname
 * @property int $credit
 * @property DataTime $registered
 * @property string $fullname
 * @property-read string $initials
 * @property-read bool $educated
 */
class MockRecord extends Record {
	
	protected $defaults = array(
		'credit' => 0,
	);

	protected $columns = array(
		'id', 'login', 'email', 'firstname', 'lastname', 'credit', 'registered'
	);

	protected $types = array(
		'id' => dibi::INTEGER,
		'login' => dibi::TEXT,
		'email' => dibi::TEXT,
		'firstname' => dibi::TEXT,
		'lastname' => dibi::TEXT,
		'credit' => dibi::INTEGER,
		'registered' => dibi::DATETIME,
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
	 * @return array
	 */
	public function getModifiedValues() {
		return parent::getModifiedValues();
	}

	/**
	 * @return array
	 */
	public function getOriginalValues() {
		return parent::getOriginalValues();
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

	/**
	 * MockRecord defined property getter
	 * @return bool
	 */
	public function isEducated() {
		return TRUE;
	}

	/**
	 * Gets bypassed property (direct from storage).
	 * @param string $atribute  property name
	 * @return mixed
	 */
	public function getBypassedAttribute($atribute) {
		return $this->storage[$atribute];
	}

}