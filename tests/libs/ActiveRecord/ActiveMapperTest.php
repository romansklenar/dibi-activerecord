<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';


/**
 * Test class for ActiveMapper.
 */
class ActiveMapperTest extends ActiveRecordDatabaseTestCase {

	public function testConnect() {
		$this->markTestSkipped();
	}

	public function testGetConnection() {
		$this->markTestSkipped();
	}

	public function testIsConnected() {
		$this->markTestSkipped();
	}

	public function testDisconnect() {
		$this->markTestSkipped();
	}



	/********************* IMapper interface test *********************/


	public function testFindFailsInvalidType() {
		$this->setExpectedException('InvalidArgumentException');
		ActiveMapper::find('NonExistingClass');
	}

	public function testFind() {
		ActiveRecordCollection::$loadImmediately = TRUE;
		
		$offices = ActiveMapper::find('Office');
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('SELECT * FROM [Offices]', strip(dibi::$sql));
		$this->assertEquals(8, $offices->count());

		$offices = ActiveMapper::find(new Office);
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('SELECT * FROM [Offices]', strip(dibi::$sql));
		$this->assertEquals(8, $offices->count());

		$offices = ActiveMapper::find('Office', array('where' => '[officeCode] > 4'));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('WHERE ([officeCode] > 4)', strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('where' => array(array('%n > %i', 'officeCode', 4))));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('WHERE', strip(dibi::$sql));
		$this->assertContains('[officeCode] > 4', strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('where' => array("[addressLine1] = '25 Old Broad Street'", "[city] = 'London'")));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains("WHERE (([addressLine1] = '25 Old Broad Street') AND ([city] = 'London'))", strip(dibi::$sql));

		# not implemented: feature of DibiFluent
		$offices = ActiveMapper::find('Office', array('where' => array('addressLine1' => '25 Old Broad Street', 'city' => 'London')));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains("WHERE (([addressLine1] = '25 Old Broad Street') AND ([city] = 'London'))", strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('order' => 'city ASC'));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('ORDER BY [city] ASC', strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('order' => 'city ASC, country DESC'));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('ORDER BY [city] ASC, [country] DESC', strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('order' => array('city' => dibi::ASC, 'country' => dibi::DESC)));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('ORDER BY [city] ASC, [country] DESC', strip(dibi::$sql));

		$offices = ActiveMapper::find('Office', array('limit' => 3));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('LIMIT 3', strip(dibi::$sql));
		$this->assertEquals(3, $offices->count());

		$offices = ActiveMapper::find('Office', array('limit' => 3, 'offset' => 4));
		$this->assertType('ActiveRecordCollection', $offices);
		$this->assertContains('LIMIT 3 OFFSET 4', strip(dibi::$sql));
		$this->assertEquals(3, $offices->count());
	}

	public function testSave() {
		$this->markTestSkipped();
	}

	public function testUpdate() {
		ActiveRecordCollection::$loadImmediately = TRUE;

		$office = ActiveMapper::find('Office', array('where' => '[officeCode] = 8'), 'first');
		$this->assertType('Office', $office);
		$office->officeCode = 555;
		ActiveMapper::update($office);
		$this->assertEquals("UPDATE [Offices] SET [officeCode]='555' WHERE ([officeCode] = '8')", strip(dibi::$sql));

		$this->setExpectedException('LogicException');
		$office = new Office;
		ActiveMapper::update($office);
	}

	public function testInsert() {
		ActiveRecordCollection::$loadImmediately = TRUE;
		
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
		ActiveMapper::insert($office);
		$this->assertEquals("INSERT INTO [Offices] ([officeCode], [city], [phone], [addressLine1], [addressLine2], [state], [country], [postalCode], [territory], [position]) VALUES ('9', 'Ostrava', '+420 595 846 854', 'Ostravska 69', NULL, 'Czech Republic', 'NA', '708 00', 'NA', 0)", strip(dibi::$sql));


		$this->setExpectedException('LogicException');
		$office = ActiveMapper::find('Office', array('where' => '[officeCode] = 8'), 'first');
		ActiveMapper::insert($office);
	}

	public function testInsertAutoincrement() {
		ActiveRecordCollection::$loadImmediately = TRUE;
		
		$employee = new Employee(array(
			'lastName' => 'Murphy',
			'firstName' => 'Diane',
			'extension' => 'x5800',
			'email' => 'dmurphy@classicmodelcars.com',
			'officeCode' => '8',
			'reportsTo' => NULL,
			'jobTitle' => 'President'
		));

		$inserted = ActiveMapper::insert($employee);
		$this->assertEquals($inserted, 1703);
		$this->assertContains('INSERT INTO [Employees]', strip(dibi::$sql));
		$this->assertNotContains('employeeNumber', strip(dibi::$sql));
	}

	public function testInsertComposedPrimaryKey() {
		$payment = new Payment(array(
			'customerNumber' => 103,
			'checkNumber' => 'HF336336',
			'paymentDate' => new DateTime('2010-01-01 12:00:00'),
			'amount' => 15.5,
		), ActiveRecord::STATE_NEW);

		$inserted = ActiveMapper::insert($payment);
		$this->assertTrue((bool) $inserted);
		$this->assertEquals("INSERT INTO [Payments] ([customerNumber], [checkNumber], [paymentDate], [amount]) VALUES (103, 'HF336336', '2010-01-01 12:00:00', 15.5)", strip(dibi::$sql));
	}

	public function testDelete() {
		$author = ActiveMapper::find('Author', array('where' => '[id] = 1'), 'first');
		$deleted = ActiveMapper::delete($author);
		$this->assertEquals(1, $deleted);
		$this->assertEquals("DELETE FROM [Authors] WHERE ([id] = 1)", strip(dibi::$sql));
	}

	public function testDeleteComposedPrimaryKey() {
		$payment = ActiveMapper::find('Payment', array('where' => array("[customerNumber] = 103", "[checkNumber] = 'HQ336336'")), 'first');
		$deleted = ActiveMapper::delete($payment);
		$this->assertEquals(1, $deleted);
		$this->assertEquals("DELETE FROM [Payments] WHERE ([customerNumber] = 103) AND ([checkNumber] = 'HQ336336')", strip(dibi::$sql));
	}



	/********************* testování práce s časem / TODO: přesunout do testu k ActiveRecordu až bude mít refactorované findery *********************/



	public function _testGetDibiDateAttribute() {
		$row = self::createResource()->fetch();
		$this->assertEquals('2004-10-19 00:00:00', $row['paymentDate']);

		$res = self::createResource();
		$res->detectTypes();
		$row = $res->fetch();
		$this->assertEquals('2004-10-19 00:00:00', $row['paymentDate']);

		$res = self::createResource();
		$res->setType('paymentDate', dibi::DATETIME);
		$row = $res->fetch();
		$this->assertEquals('2004-10-19 00:00:00', $row['paymentDate']);

		$res = self::createResource();
		$res->setType('paymentDate', dibi::TIME);
		$row = $res->fetch();
		$this->assertEquals('2004-10-19 00:00:00', $row['paymentDate']);

		$res = self::createResource();
		$res->setType('paymentDate', dibi::DATE);
		$row = self::createResource()->fetch();
		$this->assertEquals('2004-10-19', $row['paymentDate']);

	}

	public function _testGetActiveRecordDateAttribute() {
		$payment = ActiveMapper::find('Payment', array('where' => array("[customerNumber] = 103", "[checkNumber] = 'HQ336336'")), 'first');
		$this->assertEquals('2004-10-19 00:00:00', $payment['paymentDate']->format('Y-m-d H:i:s'));
		ActiveMapper::getConnection()->delete('Payments');

		$payment = new Payment(array(
			'customerNumber' => 103,
			'checkNumber' => 'HF336336',
			'paymentDate' => new DateTime('2010-01-01 12:00:00'),
			'amount' => 15.5,
		), ActiveRecord::STATE_NEW);

		$inserted = ActiveMapper::insert($payment);
		$payment = ActiveMapper::find('Payment', array('where' => array("[customerNumber] = 103", "[checkNumber] = 'HF336336'")), 'first');
		$this->assertEquals('2010-01-01 12:00:00', $payment->paymentDate->format('Y-m-d H:i:s'));
	}

	private static function createResource() {
		return ActiveMapper::getConnection()->query('SELECT * FROM Payments WHERE %and', array("[customerNumber] = 103", "[checkNumber] = 'HQ336336'"));
	}

}