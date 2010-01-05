<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';


/**
 * Test class for Mapper.
 */
class MapperTest extends ActiveRecordDatabaseTestCase {

	/** @var Mapper */
	protected $object;

	public function testAddConnection() {
		$this->markTestSkipped();
	}

	public function testGetConnection() {
		$this->markTestSkipped();
	}

	public function testDisconnect() {
		$this->markTestSkipped();
	}

	public function testGetTableInfo() {
		$r = Mapper::getTableInfo('Offices');
		$this->assertType('object', $r);
		$this->assertTrue($r instanceof DibiTableInfo);
	}

	public function getPrimaryInfo() {
		$primary = Mapper::getPrimaryInfo('Offices');
		$this->assertType('object', $primary);
		$this->assertTrue($primary instanceof DibiIndexInfo);
	}

	public function testGetColumnNames() {
		$cmp = array('productLine', 'textDescription', 'htmlDescription', 'image');

		$cols = Mapper::getColumnNames('ProductLines');
		$this->assertType('array', $cols);
		$this->assertEquals($cmp, $cols);
	}

	public function testGetColumnDefaults() {
		$cmp = array(
			'officeCode' => NULL,
			'city' => NULL,
			'phone' => NULL,
			'addressLine1' => NULL,
			'addressLine2' => NULL,
			'state' => NULL,
			'country' => NULL,
			'postalCode' => NULL,
			'territory' => NULL,
			'position' => 0,
		);

		$defaults = Mapper::getColumnDefaults('Offices');
		$this->assertType('array', $defaults);
		//$this->assertEquals($cmp, $defaults);
		$this->assertEquals($cmp['position'], (int) $defaults['position']);
	}

	public function testGetColumnTypes() {
		$cmp = array(
			'customerNumber' => dibi::INTEGER,
			'checkNumber' => dibi::TEXT,
			'paymentDate' => dibi::DATETIME,
			'amount' => dibi::FLOAT,
		);

		$types = Mapper::getColumnTypes('Payments');
		$this->assertType('array', $types);
		$this->assertEquals($cmp, $types);
	}

	public function testFind() {
		$mapper = new Mapper(new Office);

		$offices = $mapper->find();
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);

		$office = $mapper->find(1);
		$this->assertType('object', $office);
		$this->assertTrue($office instanceof Office);

		$offices = $mapper->find(3,4,5);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($offices));
		$this->assertTrue($offices->first() instanceof Office);

		$offices = $mapper->find(1,2,3,4,5);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(5, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		
		$offices = $mapper->find('[position] < 6', '[position] DESC');
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(5, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(5, $offices->first()->officeCode);

		$offices = $mapper->find('[position] > 2 AND [position] < 6', '[position] DESC, [officeCode] ASC');
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(5, $offices->first()->officeCode);

		$offices = $mapper->find(
			array('[position] > 2', '[position] < 6'),
			array('position' => 'DESC', 'officeCode' => 'ASC')
		);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(5, $offices->first()->officeCode);

		$offices = $mapper->find(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			),
			array(
				'position' => 'DESC',
				'officeCode' => 'ASC',
			)
		);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(5, $offices->first()->officeCode);

		$offices = $mapper->find('[officeCode] < 6', '[officeCode] DESC', 2);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(2, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(4, $offices->last()->officeCode);

		$offices = $mapper->find('[officeCode] < 6', '[officeCode] DESC', 2, 2);
		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(2, count($offices));
		$this->assertTrue($offices->first() instanceof Office);
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(2, $offices->last()->officeCode);
	}

	public function testCount() {
		$mapper = new Mapper(new Office);

		$this->assertEquals(8, $mapper->count());
		$this->assertEquals(1, $mapper->count(1));
		//$this->assertEquals(5, $mapper->count(1,2,3,4,5)); // is it needed?
		$this->assertEquals(5, $mapper->count('[position] < 6'));
		$this->assertEquals(3, $mapper->count('[position] > 2 AND [position] < 6'));
		$this->assertEquals(3, $mapper->count(array('[position] > 2', '[position] < 6')));
		$this->assertEquals(2, $mapper->count('[officeCode] < 6', 2));
		$this->assertEquals(2, $mapper->count('[officeCode] < 6', 2, 2));
		$this->assertEquals(3, $mapper->count(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		));
	}

	public function testUpdate() {
		$mapper = new Mapper(new Office);

		$office = $mapper->find(8);
		$office->officeCode = 555;
		$mapper->update($office);
		$this->assertEquals("UPDATE [Offices] SET [officeCode]='555' WHERE ([officeCode] = '8')", strip(dibi::$sql));
		$this->assertEquals(1, $mapper->count(555));

		$office = new Office(array(
			'officeCode' => 8,
			'city' => 'Ostrava',
			'phone' => '+420 595 846 854',
			'addressLine1' => 'Ostravska 69',
			'country' => 'NA',
			'state' => 'Czech Republic',
			'postalCode' => '708 00',
			'territory' => 'NA',
		), Office::STATE_EXISTING);

		$office->officeCode = 555;
		$mapper->update($office);
		$this->assertEquals("UPDATE [Offices] SET [officeCode]='555' WHERE ([officeCode] = '8')", strip(dibi::$sql));
		$this->assertEquals(1, $mapper->count(555));

		$this->setExpectedException('InvalidStateException');
		$office = new Office;
		$mapper->update($office); // throw InvalidStateException when updating non existing record?
	}

	public function testInsert() {
		$mapper = new Mapper(new Office);
		
		$office = new Office(array(
			'officeCode' => 9,
			'city' => 'Ostrava',
			'phone' => '+420 595 846 854',
			'addressLine1' => 'Ostravska 69',
			'country' => 'NA',
			'state' => 'Czech Republic',
			'postalCode' => '708 00',
			'territory' => 'NA',
		));

		$mapper->insert($office);
		$this->assertEquals("INSERT INTO [Offices] ([officeCode], [city], [phone], [addressLine1], [addressLine2], [state], [country], [postalCode], [territory], [position]) VALUES ('9', 'Ostrava', '+420 595 846 854', 'Ostravska 69', NULL, 'Czech Republic', 'NA', '708 00', 'NA', 0)", strip(dibi::$sql));
		$this->assertEquals(1, $mapper->count(9));

		$office = new Office(array(
			'officeCode' => 10,
			'city' => 'Ostrava',
			'phone' => '+420 595 846 854',
			'addressLine1' => 'Ostravska 69',
			'country' => 'NA',
			'state' => 'Czech Republic',
			'postalCode' => '708 00',
			'territory' => 'NA',
		));

		$mapper->insert($office);
		$this->assertEquals("INSERT INTO [Offices] ([officeCode], [city], [phone], [addressLine1], [addressLine2], [state], [country], [postalCode], [territory], [position]) VALUES ('10', 'Ostrava', '+420 595 846 854', 'Ostravska 69', NULL, 'Czech Republic', 'NA', '708 00', 'NA', 0)", strip(dibi::$sql));
		$this->assertEquals(1, $mapper->count(10));

		$this->setExpectedException('InvalidStateException');
		$office = $mapper->find(8);
		$mapper->insert($office); // throw InvalidStateException when inserting existing record?
	}

	public function testInsertAutoincrement() {
		$mapper = new Mapper(new Employee);

		$employee = new Employee(array(
			'lastName' => 'Murphy',
			'firstName' => 'Diane',
			'extension' => 'x5800',
			'email' => 'dmurphy@classicmodelcars.com',
			'officeCode' => '8',
			'reportsTo' => NULL,
			'jobTitle' => 'President'
		));

		$last = $mapper->insert($employee);
		$this->assertEquals("INSERT INTO [Employees] ([lastName], [firstName], [extension], [email], [officeCode], [reportsTo], [jobTitle]) VALUES ('Murphy', 'Diane', 'x5800', 'dmurphy@classicmodelcars.com', '8', NULL, 'President')", strip(dibi::$sql));
		$this->assertEquals(1, $mapper->count($last));
	}

	public function testDelete() {
		$mapper = new Mapper(new Office);
		$office = $mapper->find(8);
		$deleted = $mapper->delete($office);

		$this->assertEquals(1, $deleted);
		$this->assertEquals("DELETE FROM [Offices] WHERE ([officeCode] = '8')", strip(dibi::$sql));
		$this->assertEquals(0, $mapper->count(8));
	}


	public function testMagicFind() {
		$mapper = new Mapper(new Office);
		
		$offices = $mapper->findByCity('San Francisco');
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(1, count($offices));
		$this->assertEquals(1, $offices[0]->officeCode);

		$office = $mapper->findOneByCity('San Francisco');
		$this->assertTrue($office instanceof Office);
		$this->assertEquals(1, $office->officeCode);

		$offices = $mapper->findByStateAndCountry('CA', 'USA');
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(1, count($offices));
		$this->assertEquals(1, $offices[0]->officeCode);

		$office = $mapper->findOneByStateAndCountry('CA', 'USA');
		$this->assertTrue($office instanceof Office);
		$this->assertEquals(1, $office->officeCode);

		$this->setExpectedException('InvalidArgumentException');
		$mapper->findOneByStateAndCountry('CA');
	}

}