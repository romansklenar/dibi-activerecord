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

	public function testGetConnection() {
		$connection = ActiveMapper::connect($this->config, 'connection #1');
		$connection->loadFile(APP_DIR . '/models/birt/birt.structure.sql');

		$connection = ActiveMapper::connect($this->config, 'connection #2');
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');

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
			'Offices', 'Employees', 'Managers', 'Customers', 'Payments',
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

		ActiveMapper::disconnect('connection #1');
		ActiveMapper::disconnect('connection #2');
	}

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
		$this->assertEquals('id', Author::create()->primaryName);

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

	public function testGetForeign() {
		$this->assertEquals('productCode', Product::create()->foreign);
		$this->assertEquals('officeCode', Office::create()->foreign);
		$this->assertEquals('officeCode', MockOffice::create()->foreign);
		$this->assertEquals('orderNumber', Order::create()->foreign);
		$this->assertEquals('customerNumber', Customer::create()->foreign);
		$this->assertEquals('employeeNumber', Employee::create()->foreign);
		//$this->assertEquals(array('customerNumber', 'checkNumber'), Payment::create()->foreign);
		$this->assertEquals('authorId', Author::create()->foreign);

		$order = Order::create();
		$product = Product::create();
		$office = Office::create();
		$mock = MockOffice::create();
		$customer = Customer::create();
		$employee = Employee::create();
		//$payment = Payment::create();
		$author = Author::create();

		$this->assertEquals('orderNumber', $order->foreign);
		$this->assertEquals('productCode', $product->foreign);
		$this->assertEquals('officeCode', $office->foreign);
		$this->assertEquals('officeCode', $mock->foreign);
		$this->assertEquals('customerNumber', $customer->foreign);
		$this->assertEquals('employeeNumber', $employee->foreign);
		//$this->assertEquals(array('customerNumber', 'checkNumber'), $payment->foreign);
		$this->assertEquals('authorId', $author->foreign);
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



	/********************* Read assotiation tests *********************/



	public function testReadAssotiationHasOne() {
		ActiveRecordCollection::$loadImmediately = TRUE;
		
		Inflector::$railsStyle = FALSE;
		$student = Student::find(1);
		$this->assertTrue(isset($student->assignment));
		$this->assertType('Assignment', $student->assignment);
		$this->assertEquals(1, $student->assignment->id);


		Inflector::$railsStyle = TRUE;
		$guest = Guest::find(1);
		$this->assertTrue(isset($guest->car));
		$this->assertType('Car', $guest->car);
		$this->assertEquals(1, $guest->car->id);
	}

	public function testReadAssotiationBelongsTo() {
		ActiveRecordCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;
		$assignment = Assignment::find(2);
		$this->assertTrue(isset($assignment->studentId));
		$this->assertTrue(isset($assignment->student));
		$this->assertType('Student', $assignment->student);
		$this->assertEquals(2, $assignment->student->id);

		// referenced by attribute
		$student = Student::find(1);
		$this->assertTrue(isset($student->reportsTo));
		$this->assertTrue(isset($student->supervisor));
		$this->assertType('Supervisor', $student->supervisor);
		$this->assertEquals(3, $student->supervisor->id);



		Inflector::$railsStyle = TRUE;

		$car = Car::find(1);
		$this->assertFalse(isset($car->car_id));
		$this->assertTrue(isset($car->guest));
		$this->assertType('Guest', $car->guest);
		$this->assertEquals(1, $car->guest->id);

		// referenced by attribute
		$guest = Guest::find(1);
		$this->assertTrue(isset($guest->belongs_to));
		$this->assertTrue(isset($guest->guide));
		$this->assertType('Guide', $guest->guide);
		$this->assertEquals(1, $guest->guide->id);
	}
	
	public function testReadAssotiationHasMany() {
		ActiveRecordCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$programmer = Programmer::find(4);
		$this->assertType('ActiveRecordCollection', $programmer->tasks);
		$this->assertEquals(2, count($programmer->tasks));
		$this->assertType('Task', $task = $programmer->tasks->first());
		$this->assertEquals(1003, $task->id);
		$this->assertType('Task', $task = $programmer->tasks->last());
		$this->assertEquals(1009, $task->id);

		$project = Project::find(1);
		$this->assertType('ActiveRecordCollection', $project->tasks);
		$this->assertEquals(5, count($project->tasks));
		$this->assertType('Task', $task = $project->tasks->first());
		$this->assertEquals(1001, $task->id);
		$this->assertType('Task', $task = $project->tasks->last());
		$this->assertEquals(1005, $task->id);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$this->assertType('ActiveRecordCollection', $food->compositions);
		$this->assertEquals(6, count($food->compositions));
		$this->assertType('Composition', $composition = $food->compositions->first());
		$this->assertEquals(105, $composition->id);
		$this->assertType('Composition', $composition = $food->compositions->last());
		$this->assertEquals(110, $composition->id);

		$ingredient = Ingredient::find(7);
		$this->assertType('ActiveRecordCollection', $ingredient->compositions);
		$this->assertEquals(3, count($ingredient->compositions));
		$this->assertType('Composition', $composition = $ingredient->compositions->first());
		$this->assertEquals(102, $composition->id);
		$this->assertType('Composition', $composition = $ingredient->compositions->last());
		$this->assertEquals(110, $composition->id);
	}

	public function testReadAssotiationHasManyViaThrough() {
		ActiveRecordCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$programmer = Programmer::find(4);
		$this->assertType('ActiveRecordCollection', $programmer->projects);
		$this->assertEquals(2, count($programmer->projects));
		$this->assertType('Project', $project = $programmer->projects->first());
		$this->assertEquals(1, $project->id);
		$this->assertType('Project', $project = $programmer->projects->last());
		$this->assertEquals(3, $project->id);

		$project = Project::find(1);
		$this->assertType('ActiveRecordCollection', $project->programmers);
		$this->assertEquals(5, count($project->programmers));
		$this->assertType('Programmer', $programmer = $project->programmers->first());
		$this->assertEquals(1, $programmer->id);
		$this->assertType('Programmer', $programmer = $project->programmers->last());
		$this->assertEquals(7, $programmer->id);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$this->assertType('ActiveRecordCollection', $food->ingredients);
		$this->assertEquals(6, count($food->ingredients));
		$this->assertType('Ingredient', $ingredient = $food->ingredients->first());
		$this->assertEquals(2, $ingredient->id);
		$this->assertType('Ingredient', $ingredient = $food->ingredients->last());
		$this->assertEquals(8, $ingredient->id);

		$ingredient = Ingredient::find(7);
		$this->assertType('ActiveRecordCollection', $ingredient->foods);
		$this->assertEquals(3, count($ingredient->foods));
		$this->assertType('Food', $food = $ingredient->foods->first());
		$this->assertEquals(1, $food->id);
		$this->assertType('Food', $food = $ingredient->foods->last());
		$this->assertEquals(3, $food->id);
	}

	public function testReadAssotiationHasManyAndBelongsTo() {
		ActiveRecordCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$post = Post::find(4);
		$this->assertType('ActiveRecordCollection', $post->tags);
		$this->assertEquals(2, count($post->tags));
		$this->assertType('Tag', $tag = $post->tags->first());
		$this->assertEquals(1, $tag->id);
		$this->assertType('Tag', $tag = $post->tags->last());
		$this->assertEquals(3, $tag->id);

		$tag = Tag::find(1);
		$this->assertType('ActiveRecordCollection', $tag->posts);
		$this->assertEquals(5, count($tag->posts));
		$this->assertType('Post', $post = $tag->posts->first());
		$this->assertEquals(1, $post->id);
		$this->assertType('Post', $post = $tag->posts->last());
		$this->assertEquals(7, $post->id);



		Inflector::$railsStyle = TRUE;

		$album = Album::find(3);
		$this->assertType('ActiveRecordCollection', $album->songs);
		$this->assertEquals(6, count($album->songs));
		$this->assertType('Song', $song = $album->songs->first());
		$this->assertEquals(2, $song->id);
		$this->assertType('Song', $song = $album->songs->last());
		$this->assertEquals(8, $song->id);

		$song = Song::find(7);
		$this->assertType('ActiveRecordCollection', $song->albums);
		$this->assertEquals(3, count($song->albums));
		$this->assertType('Album', $album = $song->albums->first());
		$this->assertEquals(1, $album->id);
		$this->assertType('Album', $album = $song->albums->last());
		$this->assertEquals(3, $album->id);
	}



	/********************* Write assotiation tests *********************/



	public function testWriteAssotiationHasOne() {
		$this->markTestIncomplete();
	}

	public function testWriteAssotiationHasMany() {
		$this->markTestIncomplete();
	}

	public function testWriteAssotiationHasManyViaThrough() {
		$this->markTestIncomplete();
	}

	public function testWriteAssotiationBelongsTo() {
		$this->markTestIncomplete();
	}

	public function testWriteAssotiationHasManyAndBelongsTo() {
		$this->markTestIncomplete();
	}


	/********************* Executors tests *********************/


	public function testCount() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

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

	public function testStaticObjectsCount() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");
		
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
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");
		
		$this->assertType('Order', Order::objects()->first());
		$this->assertType('Product', Product::objects()->first());
		$this->assertType('Office', Office::objects()->first());
		$this->assertType('MockOffice', MockOffice::objects()->first());
		$this->assertType('Customer', Customer::objects()->first());
		$this->assertType('Employee', Employee::objects()->first());
		$this->assertType('Payment', Payment::objects()->first());
		$this->assertType('Author', Author::objects()->first());
	}

	public function testStaticCreate() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
		);

		$author = Author::create();
		$this->assertType('Author', $author);
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
		$this->assertType('Author', $author);
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
		$this->assertType('Author', $author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());


		$author = Author::create(new ArrayObject($this->authorValues));
		$this->assertType('Author', $author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());


		$author = Author::create(new ArrayObject($this->authorValues + array('non-existing-column' => '***')));
		$this->assertType('Author', $author);
		$this->assertType('array', $author->values);
		$this->assertEquals(6, count($author->values));
		$this->assertEquals($cmp, $author->values);
		$this->assertTrue($author->isRecordNew());
		$this->assertFalse($author->isRecordExisting());

		$this->assertEquals(3, Author::count());

		$this->setExpectedException('InvalidArgumentException');
		$author = Author::create("*** not compatible ***");
	}

	public function testStaticFindAll() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

		$authors = Author::find(1,2);
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(2, count($authors));
		$this->assertEquals(1, $authors[0]->id);
		$this->assertFalse($authors[0]->isRecordNew());
		$this->assertTrue($authors[0]->isRecordExisting());
		$this->assertEquals(2, $authors[1]->id);
		$this->assertFalse($authors[1]->isRecordNew());
		$this->assertTrue($authors[1]->isRecordExisting());

		$authors = Author::find('1','2');
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(2, count($authors));
		$this->assertEquals(1, $authors[0]->id);
		$this->assertFalse($authors[0]->isRecordNew());
		$this->assertTrue($authors[0]->isRecordExisting());
		$this->assertEquals(2, $authors[1]->id);
		$this->assertFalse($authors[1]->isRecordNew());
		$this->assertTrue($authors[1]->isRecordExisting());

		$products = Product::find('S10_1678', 'S24_2000');
		$this->assertType('ActiveRecordCollection', $products);
		$this->assertEquals(2, count($products));
		$this->assertEquals('S10_1678', $products->first()->productCode);
		$this->assertEquals('S24_2000', $products->last()->productCode);

		$author = Author::find(array('[id] = 3'));
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		// alternative way of previous command
		$author = Author::find(3);
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		$authors = Author::findAll(
			array(
				array('%n >= %i', 'credit', 100),
				array('%n <= %i', 'credit', 300),
			),
			array(
				'credit' => 'ASC'
			)
		);
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(2, count($authors));
		$this->assertTrue($authors[0]->credit <= $authors[1]->credit);
		$this->assertGreaterThanOrEqual($authors[0]->credit, $authors[1]->credit);
	}

	public function testStaticFind() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

		$author = Author::find();
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);

		$author = Author::find(1);
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);

		$author = Author::find(array('[id] = 3'));
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(3, $author->id);

		$author = Author::find(array("[firstname] = 'John'"));
		$this->assertType('Author', $author);
		$this->assertFalse($author->isRecordNew());
		$this->assertTrue($author->isRecordExisting());
		$this->assertEquals(1, $author->id);

		$author = Author::find(
			array(
				array('%n >= %i', 'credit', 100),
				array('%n <= %i', 'credit', 300),
			),
			array(
				'credit' => 'ASC'
			)
		);
		$this->assertTrue($author->credit >= 100 && $author->credit <= 300);
	}

	public function testStaticCount() {
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

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
		$this->assertType('Author', $author);
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
		$tmp = Author::findAll(array('[id] = 55'))->first();
		$this->assertType('Author', $tmp);
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
		$this->assertType('Author', $author);
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
		$this->assertType('Author', $author);
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
		if (!TestHelper::isPhpVersion('5.3'))
			$this->markTestSkipped("Test is only for PHP 5.3.*");

		$author = Author::findByLogin('john007');
		$this->assertType('Author', $author);
		$this->assertEquals('john007', $author->login);

		$authors = Author::findAllByFirstname('John');
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(1, count($authors));
		$this->assertType('Author', $authors->first());
		$this->assertEquals('John', $author->firstname);

		$author = Author::findByFirstname('John');
		$this->assertType('Author', $author);
		$this->assertEquals('John', $author->firstname);

		$authors = Author::findAllByLastname('Doe');
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(3, count($authors));
		$this->assertType('Author', $authors->first());

		$authors = Author::findAllByFirstnameAndLastname('John', 'Doe');
		$this->assertType('ActiveRecordCollection', $authors);
		$this->assertEquals(1, count($authors));
		$this->assertType('Author', $authors->first());
		$this->assertEquals('John', $author->firstname);
		$this->assertEquals('Doe', $author->lastname);

		$author = Author::findByFirstnameAndLastname('John', 'Doe');
		$this->assertType('Author', $author);
		$this->assertEquals('John', $author->firstname);
		$this->assertEquals('Doe', $author->lastname);

		$this->setExpectedException('InvalidArgumentException');
		Author::findByFirstnameAndLastname('John');
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
