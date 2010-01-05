<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';

/**
 * Test class for ActiveRecord.
 */
class ActiveRecordTest extends ActiveRecordDatabaseTestCase {

	/** @var array  test input data */
	private $authorValues = array(
		'login' => 'john007',
		'email' => 'john.doe@example.com',
		'lastname' => 'Doe',
	);


	public function testGetTableName() {
		$this->assertEquals('Products', Product::create()->tableName);
		$this->assertEquals('Offices', Office::create()->tableName);
		$this->assertEquals('Offices', MockOffice::create()->tableName);
		$this->assertEquals('Orders', Order::create()->tableName);
		$this->assertEquals('Customers', Customer::create()->tableName);
		$this->assertEquals('Employees', Employee::create()->tableName);
		$this->assertEquals('Payments', Payment::create()->tableName);

		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		$payment = Payment::create();
		$author = Author::create();

		$this->assertEquals('Orders', $order->tableName);
		$this->assertEquals('Products', $product->tableName);
		$this->assertEquals('Offices', $office->tableName);
		$this->assertEquals('Offices', $mock->tableName);
		$this->assertEquals('Customers', $customer->tableName);
		$this->assertEquals('Employees', $employee->tableName);
		$this->assertEquals('Payments', $payment->tableName);
		$this->assertEquals('Authors', $author->tableName);
	}

	public function testGetPrimaryName() {
		$this->assertEquals('productCode', Product::create()->primaryName);
		$this->assertEquals('officeCode', Office::create()->primaryName);
		$this->assertEquals('officeCode', MockOffice::create()->primaryName);
		$this->assertEquals('orderNumber', Order::create()->primaryName);
		$this->assertEquals('customerNumber', Customer::create()->primaryName);
		$this->assertEquals('employeeNumber', Employee::create()->primaryName);
		$this->assertEquals(array('customerNumber', 'checkNumber'), Payment::create()->primaryName);

		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		$payment = Payment::create();
		$author = Author::create();

		$this->assertEquals('orderNumber', $order->primaryName);
		$this->assertEquals('productCode', $product->primaryName);
		$this->assertEquals('officeCode', $office->primaryName);
		$this->assertEquals('officeCode', $mock->primaryName);
		$this->assertEquals('customerNumber', $customer->primaryName);
		$this->assertEquals('employeeNumber', $employee->primaryName);
		$this->assertEquals(array('customerNumber', 'checkNumber'), $payment->primaryName);
		$this->assertEquals('id', $author->primaryName);
	}

	public function testGetMapper() {
		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		$payment = Payment::create();
		$author = Author::create();
		
		$this->assertTrue($order->mapper instanceof IMapper);
		$this->assertTrue($product->mapper instanceof IMapper);
		$this->assertTrue($office->mapper instanceof IMapper);
		$this->assertTrue($mock->mapper instanceof IMapper);
		$this->assertTrue($customer->mapper instanceof IMapper);
		$this->assertTrue($employee->mapper instanceof IMapper);
		$this->assertTrue($payment->mapper instanceof IMapper);
		$this->assertTrue($author->mapper instanceof IMapper);


		$this->assertTrue($order->mapper instanceof Mapper);
		$this->assertTrue($product->mapper instanceof Mapper);
		$this->assertTrue($office->mapper instanceof Mapper);
		$this->assertTrue($mock->mapper instanceof Mapper);
		$this->assertTrue($customer->mapper instanceof Mapper);
		$this->assertTrue($employee->mapper instanceof Mapper);
		$this->assertTrue($payment->mapper instanceof Mapper);
		$this->assertTrue($author->mapper instanceof Mapper);

		$this->assertEquals('Order', $order->getMapper()->getRowClass());
		$this->assertEquals('Product', $product->getMapper()->getRowClass());
		$this->assertEquals('Office', $office->getMapper()->getRowClass());
		$this->assertEquals('MockOffice', $mock->getMapper()->getRowClass());
		$this->assertEquals('Customer', $customer->getMapper()->getRowClass());
		$this->assertEquals('Employee', $employee->getMapper()->getRowClass());
		$this->assertEquals('Payment', $payment->getMapper()->getRowClass());
		$this->assertEquals('Author', $author->getMapper()->getRowClass());
	}

	public function testGetConnection() {
		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/birt.structure.sql');
		Mapper::addConnection($connection, 'connection #1');

		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');
		Mapper::addConnection($connection, 'connection #2');

		$customer = MockCustomer::create();
		$consumer = MockConsumer::create();

		$this->assertTrue($customer->getConnection()->getDatabaseInfo()->hasTable($customer->tableName));
		$this->assertTrue($consumer->getConnection()->getDatabaseInfo()->hasTable($consumer->tableName));
		$this->assertFalse($customer->getConnection()->getDatabaseInfo()->hasTable($consumer->tableName));
		$this->assertFalse($consumer->getConnection()->getDatabaseInfo()->hasTable($customer->tableName));

		
		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		$payment = Payment::create();
		$author = Author::create();

		$tables = array(
			'Offices', 'Employees', 'Customers', 'Payments',
			'ProductLines', 'Products', 'Orders', 'OrderDetails'
		);
		$this->assertEquals($tables, $order->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $product->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $office->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $mock->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $customer->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $employee->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals($tables, $payment->getConnection()->getDatabaseInfo()->getTableNames());
		$this->assertEquals(array('Authors'), $author->getConnection()->getDatabaseInfo()->getTableNames());

		Mapper::disconnect('connection #1');
		Mapper::disconnect('connection #2');
	}

	public function testCount() {
		$this->assertEquals(326, Order::count());
		$this->assertEquals(110, Product::count());
		$this->assertEquals(8, Office::count());
		$this->assertEquals(8, MockOffice::count());
		$this->assertEquals(122, Customer::count());
		$this->assertEquals(23, Employee::count());
		$this->assertEquals(273, Payment::count());
		$this->assertEquals(3, Author::count());
		
		$this->assertEquals(1, Office::count(1));
		$this->assertEquals(1, MockOffice::count(1));
		$this->assertEquals(1, Author::count(1));

		$this->markTestIncomplete();
		// TODO: ostatni varianty
	}

	public function testMapperCount() {
		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		$payment = Payment::create();
		$author = Author::create();

		$this->assertEquals(326, $order->mapper->count());
		$this->assertEquals(110, $product->mapper->count());
		$this->assertEquals(8, $office->mapper->count());
		$this->assertEquals(8, $mock->mapper->count());
		$this->assertEquals(122, $customer->mapper->count());
		$this->assertEquals(23, $employee->mapper->count());
		$this->assertEquals(273, $payment->mapper->count());
		$this->assertEquals(3, $author->mapper->count());

		$this->assertEquals(1, $office->mapper->count(1));
		$this->assertEquals(1, $mock->mapper->count(1));
		$this->assertEquals(1, $author->mapper->count(1));

		$this->markTestIncomplete();
		// TODO: ostatni varianty
	}

	public function testStaticObjectsCount() {
		$this->assertEquals(326, Order::objects()->count());
		$this->assertEquals(110, Product::objects()->count());
		$this->assertEquals(8, Office::objects()->count());
		$this->assertEquals(8, MockOffice::objects()->count());
		$this->assertEquals(122, Customer::objects()->count());
		$this->assertEquals(23, Employee::objects()->count());
		$this->assertEquals(273, Payment::objects()->count());
		$this->assertEquals(3, Author::objects()->count());
	}

	public function testStaticObjects() {
		$this->assertTrue(Order::objects()->first() instanceof Order);
		$this->assertTrue(Product::objects()->first() instanceof Product);
		$this->assertTrue(Office::objects()->first() instanceof Office);
		$this->assertTrue(MockOffice::objects()->first() instanceof MockOffice);
		$this->assertTrue(Customer::objects()->first() instanceof Customer);
		$this->assertTrue(Employee::objects()->first() instanceof Employee);
		$this->assertTrue(Payment::objects()->first() instanceof Payment);
		$this->assertTrue(Author::objects()->first() instanceof Author);
	}

	public function testGetState() {

		$values = array(
			'id' => NULL, // not defined value of PK
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$author = new Author($values);
		// Record is simple (doesn't know anything about primary keys)
		// but ActiveRecord will detect: if PK value is empty => new record
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());
		$author = Author::create($values);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());


		$values = array(
			'id' => 11,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$author = new Author($values);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$author = Author::create($values); // explicitly says that record is NEW
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$values = array(
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'lastname' => 'Doe',
		);

		$author = new Author($values);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());
		$author = Author::create($values);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$author = new Author(array());
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());
		$author = Author::create($values);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$author = new Author();
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());
		$author = Author::create();
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$author = new Author($this->authorValues, Author::STATE_EXISTING);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$author = Author::create($this->authorValues); // explicitly says that record is NEW
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$author = new Author($this->authorValues, Author::STATE_NEW);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());
		$author = Author::create($this->authorValues);
		$this->assertFalse($author->isRecordExisting());
		$this->assertTrue($author->isRecordNew());

		$author = Author::create($this->authorValues);
		$author->destroy();
		$this->assertFalse($author->isRecordExisting());
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordDeleted());
		$this->assertTrue($author->isFrozen());


		$values = array(
			'officeCode' => 8,
			'city' => 'Ostrava',
			'phone' => '+420 595 846 854',
			'addressLine1' => 'Ostravska 69',
			'country' => 'NA',
			'state' => 'Czech Republic',
			'postalCode' => '708 00',
			'territory' => 'NA',
		);

		// created by instance => new record
		$office = new Office($values);
		$this->assertTrue($office->isRecordNew());
		$this->assertFalse($office->isRecordExisting());

		// created by factory => new record
		$office = Office::create($values);
		$this->assertTrue($office->isRecordNew());
		$this->assertFalse($office->isRecordExisting());

		// explicitly setup state to existing
		$office = new Office($values, Office::STATE_EXISTING);
		$this->assertTrue($office->isRecordExisting());
		$this->assertFalse($office->isRecordNew());

		// explicitly setup state to new => new record
		$office = new Office($values, Office::STATE_NEW);
		$this->assertTrue($office->isRecordNew());
		$this->assertFalse($office->isRecordExisting());

		// primary is not set => new record
		unset($values['officeCode']);
		$office = new Office($values);
		$this->assertTrue($office->isRecordNew());
		$this->assertFalse($office->isRecordExisting());

		// TODO? explicitly setup state to existing, but without data => detect => new record
		//$office = new Office(array(), Office::STATE_EXISTING);
		//$this->assertTrue($office->isRecordNew());
		//$this->assertFalse($office->isRecordExisting());

		$this->setExpectedException('InvalidArgumentException');
		$author = new Office($this->authorValues, '*** invalid ***');
	}

	public function testStaticCreate() {
		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
		);

		$author = Author::create();
		$this->assertTrue($author instanceof Author);
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());

		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$author = Author::create($this->authorValues);
		$this->assertTrue($author instanceof Author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());

		$cmp = array(
			'id' => NULL,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => NULL,
			'lastname' => 'Doe',
			'credit' => 0,
		);

		$author = Author::create($this->authorValues + array('non-existing-column' => '***'));
		$this->assertTrue($author instanceof Author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());


		$author = Author::create(new ArrayObject($this->authorValues));
		$this->assertTrue($author instanceof Author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());


		$author = Author::create(new ArrayObject($this->authorValues + array('non-existing-column' => '***')));
		$this->assertTrue($author instanceof Author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());

		$this->assertEquals(3, Author::count());

		$this->setExpectedException('InvalidArgumentException');
		$author = Author::create("*** not compatible ***");
	}

	public function testStaticFind() {
		$authors = Author::find();
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($authors));

		$author = $authors->first();
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);

		$author = $authors->last();
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		$authors = Author::find(
			array(
				array('%n >= %i', 'credit', 100),
				array('%n <= %i', 'credit', 300),
			),
			array(
				'credit' => 'ASC'
			)
		);
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(2, count($authors));
		$this->assertTrue($authors[0]->credit <= $authors[1]->credit);
		$this->assertGreaterThanOrEqual($authors[0]->credit, $authors[1]->credit);

		$authors = Author::find(1,2);
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(2, count($authors));
		$this->assertEquals(1, $authors[0]->id);
		$this->assertFalse($authors[0]->isRecordNew());
		$this->assertTrue($authors[0]->isRecordExisting());
		$this->assertEquals(2, $authors[1]->id);
		$this->assertFalse($authors[1]->isRecordNew());
		$this->assertTrue($authors[1]->isRecordExisting());

		$author = Author::find(array('[id] = 3'))->first();
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		// alternative way of previous command
		$author = Author::find(3);
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);
	}

	public function testStaticFindOne() {
		$author = Author::findOne();
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);

		$author = Author::findOne(array('[id] = 3'));
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		$author = Author::findOne(array("[firstname] = 'John'"));
		$this->assertTrue($author instanceof Author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);
	}

	public function testStaticCount() {
		$this->assertEquals(3, Author::count());
		$this->assertEquals(2, Author::count(array(
			array('%n > %i', 'credit', 100),
		)));
		$this->assertEquals(1, Author::count(array(
			array('%n >= %i', 'credit', 150),
			array('%n <= %i', 'credit', 300),
		)));
	}

	public function testSave() {
		$cmp = array(
			'id' => NULL,
			'login' => 'johny007',
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
			'credit' => 195,
		);

		$author = Author::create($cmp);
		$this->assertTrue($author instanceof Author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());
		$this->assertEquals(3, Author::count());

		$author->save();
		$cmp['id'] = 4;
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals($cmp, $author->values);
		$this->assertEquals(4, Author::count());

		$author->id = 55;
		$author->save();

		$this->assertEquals(1, Author::count(array('[id] = 55')));
		$tmp = Author::find(array('[id] = 55'))->first();
		$this->assertTrue($tmp instanceof Author);
	}

	public function testSaveWithEmptyMandatoryField() {
		$values = array(
			//'id' => NULL, // primary autoincrement
			//'login' => NULL, // not null without default
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
			'credit' => 195,
		);
		$cmp = array('id' => NULL, 'login' => NULL) + $values;

		$mod = array(
			'login%s' => NULL,
			'email%s' => 'johny.doe@example.com',
			'firstname%s' => 'Johny',
			'lastname%s' => 'Doe',
			'credit%i' => 195,
		);

		$author = Author::create($values);
		$this->assertTrue($author instanceof Author);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertEquals($mod, $author->modifiedValues);
		$this->assertEquals(3, Author::count());

		$this->setExpectedException('DibiDriverException');
		$author->save();
	}

	public function testSaveWithEmptyMandatoryFieldOnInstance() {
		$values = array(
			//'id' => NULL, // primary autoincrement
			//'login' => NULL, // not null without default
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
			'credit' => 195,
		);
		$cmp = array('id' => NULL, 'login' => NULL) + $values;

		$mod = array(
			'login%s' => NULL,
			'email%s' => 'johny.doe@example.com',
			'firstname%s' => 'Johny',
			'lastname%s' => 'Doe',
			'credit%i' => 195,
		);

		$author = new Author($values);
		$this->assertTrue($author instanceof Author);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertEquals($mod, $author->modifiedValues);
		$this->assertEquals(3, Author::count());

		$this->setExpectedException('DibiDriverException');
		$author->save();
	}

	public function testDestroy() {
		$author = Author::find(1);
		$author->destroy();
		$this->assertTrue($author->isFrozen());
		$this->assertEquals(0, Author::count(array('[id] = 1')));
	}

	public function testMagicFind() {
		$author = Author::findOneByLogin('john007');
		$this->assertTrue($author instanceof Author);
		$this->assertEquals('john007', $author->login);

		$authors = Author::findByFirstname('John');
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(1, count($authors));
		$this->assertTrue($authors->first() instanceof Author);
		$this->assertEquals('John', $author->firstname);

		$author = Author::findOneByFirstname('John');
		$this->assertTrue($author instanceof Author);
		$this->assertEquals('John', $author->firstname);

		$authors = Author::findByLastname('Doe');
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($authors));
		$this->assertTrue($authors->first() instanceof Author);

		$authors = Author::findByFirstnameAndLastname('John', 'Doe');
		$this->assertTrue($authors instanceof ActiveRecordCollection);
		$this->assertEquals(1, count($authors));
		$this->assertTrue($authors->first() instanceof Author);
		$this->assertEquals('John', $author->firstname);
		$this->assertEquals('Doe', $author->lastname);

		$author = Author::findOneByFirstnameAndLastname('John', 'Doe');
		$this->assertTrue($author instanceof Author);
		$this->assertEquals('John', $author->firstname);
		$this->assertEquals('Doe', $author->lastname);

		$this->setExpectedException('InvalidArgumentException');
		Author::findOneByFirstnameAndLastname('John');
	}

}




class MockOffice extends Office {
	
	protected static $table = 'Offices';
	protected static $primary = 'officeCode';


	public function getCache() {
		return self::$cache;
	}
}

class MockCustomer extends Customer {

	protected static $table = 'Customers';
	protected static $primary = 'customerNumber';
	protected static $connection = 'connection #1';
}

class MockConsumer extends Consumer {

	protected static $table = 'Consumers';
	protected static $primary = 'id';
	protected static $connection = 'connection #2';
}

abstract class MockActiveRecord extends ActiveRecord {

	public function parseAssotiations() {
		return parent::parseAssotiations();
	}

	public function getAssotiations() {
		return parent::getAssotiations();
	}

	public function getAssotiation($toTable) {
		return parent::getAssotiation($toTable);
	}
}
