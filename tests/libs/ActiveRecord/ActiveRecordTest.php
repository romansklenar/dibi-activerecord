<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';

/**
 * Test class for ActiveRecord.
 */
class ActiveRecordTest extends ActiveRecordDatabaseTestCase {

	function testConstruct() {
		$author = new Author(array('id' => 4)); // has default value 0 for column 'credit' by database
		$this->assertTrue($author->isNewRecord());
		$this->assertTrue($author->isDirty());
		$this->assertEquals(array('id' => 4, 'credit' => 0), (array) $author->originals);
		$this->assertEquals(array('id' => 4, 'credit' => 0), (array) $author->changes);
		$this->assertEquals(0, $author->originals->credit);
		$this->assertEquals(0, $author->changes->credit);
	}

	function testEmptyConstruct() {
		$author = new Author; // has default value 0 for column 'credit' by database
		$this->assertTrue($author->isNewRecord());
		$this->assertTrue($author->isDirty());
		$this->assertEquals(array('credit' => 0), (array) $author->originals);
		$this->assertEquals(array('credit' => 0), (array) $author->changes);
		$this->assertEquals(0, $author->originals->credit);
		$this->assertEquals(0, $author->changes->credit);
	}

	function testConstructWithObject() {
		$student = new Student(array('assignment' => new Assignment));
		$this->assertTrue($student->isNewRecord());
		$this->assertTrue($student->isDirty());
		$this->assertType('Assignment', $student->assignment);
		$this->assertTrue($student->assignment->isNewRecord());
		$this->assertTrue($student->assignment->isDirty());
	}

	function testConstructByDibi() {
		$author = Author::getDataSource()->where('[id] = 1')->getResult()->setRowClass('Author')->fetch();		
		$cmp = array(
			'id' => 1,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => 'John',
			'lastname' => 'Doe',
			'credit' => 300,
		);
		
		$this->assertTrue($author->isExistingRecord());
		$this->assertFalse($author->isDirty());
		$this->assertEquals($cmp, (array) $author->originals);
		$this->assertEquals(array(), (array) $author->changes);
	}

	function testGetClass() {
		$this->assertEquals('Author', Author::getClass());
	}

	function testGetReflection() {
		$rc = Author::getReflection();
		$this->assertType('ClassReflection', $rc);
		$this->assertEquals('Author', $rc->name);
	}

	/**
	 * @covers ActiveRecord::getConnectionName
	 * @covers ActiveRecord::getConnection
	 */
	function testGetConnection() {
		$conn = Author::getConnection();
		$this->assertType('DibiConnection', $conn);
		$this->assertEquals('#authors', $conn->getConfig('name'));
	}

	function testGetDataSource() {
		$ds = Author::getDataSource();
		$this->assertType('DibiDataSource', $ds);
		$this->assertEquals('SELECT * FROM [Authors]', strip((string) $ds));
	}

	function testGetTableName() {
		Inflector::$railsStyle = FALSE;
		$this->assertEquals('Authors', Author::getTableName());
		$this->assertEquals('OrderDetails', OrderDetail::getTableName());
	}

	function testGetTableWithRailsStyle() {
		Inflector::$railsStyle = TRUE;
		$this->assertEquals('foods', Food::getTableName());
		$this->assertEquals('product_lines', ProductLine::getTableName());
	}

	function testTableInfo() {
		$info = Author::getTableInfo();
		$this->assertType('DibiTableInfo', $info);
		$this->assertEquals('Authors', $info->name);
	}

	function testPrimaryKey() {
		$this->assertEquals('id', Author::getPrimaryKey());

		$this->markTestIncomplete();
		// TODO: test na slozeny klic
	}

	function testPrimaryInfo() {
		$primary = Author::getPrimaryInfo();
		$this->assertType('DibiIndexInfo', $primary);
		$this->assertEquals(1, count($primary->columns));
		
		$this->markTestIncomplete();
		// TODO: test na record s uzivatelsky definovanym klicem
	}

	function testForeignKey() {
		Inflector::$railsStyle = FALSE;
		$this->assertEquals('authorId', Author::getForeignKey());
	}

	function testForeignKeyWithRailsStyle() {
		Inflector::$railsStyle = TRUE;
		$this->assertEquals('food_id', Food::getForeignKey());
	}

	function testGetColumns() {
		$this->markTestSkipped('Private method');
		$cmp = array('id', 'login', 'email', 'firstname', 'lastname', 'credit');
		$this->assertEquals($cmp, Author::getColumnNames());
	}

	function testGetAssociations() {
		$this->assertEquals(array(), Author::getAssociations());
	}

	function testGetTypes() {
		$author = new Author;
		$cmp = array(
			'id' => dibi::INTEGER, 'login' => dibi::TEXT,
			'email' => dibi::TEXT, 'firstname' => dibi::TEXT,
			'lastname' => dibi::TEXT, 'credit' => dibi::INTEGER
		);

		$this->assertEquals($cmp, Author::getTypes());
		$this->assertEquals($cmp, $author->types);
	}
/*
	function testGetDefaults() {
		$author = new Author;
		$cmp = array(
			'id' => NULL, 'login' => NULL,
			'email' => NULL, 'firstname' => NULL,
			'lastname' => NULL, 'credit' => 0
		);

		$this->assertEquals($cmp, Author::getDefaults());
		$this->assertEquals($cmp, $author->defaults);
	}
*/
	function testCreate() {
		$values = array(
			'login' => 'johny007',
			'email' => 'johny.moe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Moe',
			'credit' => 123,
		);
		$author = Author::create($values);
		$this->assertTrue($author->isExistingRecord());
		$this->assertFalse($author->isDirty());
		$this->assertType('Author', $author);
	}

	function testObjects() {
		$authors = Author::objects();
		$this->assertType('ActiveCollection', $authors);
	}
/*
	function testCount() {
		$this->assertEquals(3, Author::count());
		Debug::dump(strip(dibi::$sql));
	}

	function testAvarage() {
		$this->assertEquals(2, Author::avarage('id'));
		Debug::dump(strip(dibi::$sql));
	}

	function testMinimum() {
		$this->assertEquals(1, Author::minimum('id'));
		Debug::dump(strip(dibi::$sql));
	}

	function testMaximum() {
		$this->assertEquals(3, Author::maximum('id'));
		Debug::dump(strip(dibi::$sql));
	}

	function testSum() {
		$this->assertEquals(6, Author::sum('id'));
		Debug::dump(strip(dibi::$sql));
	}
*/
	function testFind() {
		$author = Author::find(1);
		$this->assertType('Author', $author);
		$this->assertEquals(1, $author->id);
	}

	function testFindAll() {
		$authors = Author::objects();
		$this->assertType('ActiveCollection', $authors);
	}

	function testStaticMagicFind() {
		$author = Author::findById(1);
		$this->assertType('Author', $author);
		$this->assertEquals(1, $author->id);
	}

	function testStaticMagicFindAll() {
		$authors = Author::findAllById(array(1,2));
		$this->assertType('ActiveCollection', $authors);
		$this->assertEquals(2, $authors->count());
	}

	function testInstanceMagicFindAll() {
		$author = new Author;
		$authors = $author->findAllById(array(1,2));
		$this->assertType('ActiveCollection', $authors);
		$this->assertEquals(2, $authors->count());
	}

	function testGetChangesAndGetOriginals() {
		$author = Author::findById(2);
		$this->assertFalse($author->isDirty());
		$this->assertEquals(50, $author->credit);
		$author->credit = 50;
		$this->assertEquals(array(), (array) $author->changes);
		$this->assertFalse($author->isDirty());
		$origin = $author->values;
		$author->credit = 100;
		$this->assertTrue($author->isDirty());
		$this->assertEquals(array('credit' => 100), (array) $author->changes);
		$this->assertEquals($origin, (array) $author->originals);
	}

	function testWritabilityOfGetChangesAndGetOriginals() {
		$author = Author::findById(2);
		$this->assertEquals(0, count($author->changes));
		$author->changes->id = 5; // nesmi ovlivnit hodnotu
		$this->assertEquals(0, count($author->changes));

		$author = Author::findById(2);
		$this->assertEquals(2, $author->originals->id);
		$author->originals->id = 5; // nesmi ovlivnit hodnotu
		$this->assertEquals(2, $author->originals->id);
	}
	
	
	
	/********************* Read association tests *********************/



	public function testReadAssociationHasOne() {
		ActiveCollection::$loadImmediately = TRUE;

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

	public function testReadAssociationBelongsTo() {
		ActiveCollection::$loadImmediately = TRUE;

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

	public function testReadAssociationHasMany() {
		ActiveCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$programmer = Programmer::find(4);
		$this->assertType('ActiveCollection', $programmer->tasks);
		$this->assertEquals(2, count($programmer->tasks));
		$this->assertType('Task', $task = $programmer->tasks->first());
		$this->assertEquals(1003, $task->id);
		$this->assertType('Task', $task = $programmer->tasks->last());
		$this->assertEquals(1009, $task->id);

		$project = Project::find(1);
		$this->assertType('ActiveCollection', $project->tasks);
		$this->assertEquals(5, count($project->tasks));
		$this->assertType('Task', $task = $project->tasks->first());
		$this->assertEquals(1001, $task->id);
		$this->assertType('Task', $task = $project->tasks->last());
		$this->assertEquals(1005, $task->id);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$this->assertType('ActiveCollection', $food->compositions);
		$this->assertEquals(6, count($food->compositions));
		$this->assertType('Composition', $composition = $food->compositions->first());
		$this->assertEquals(105, $composition->id);
		$this->assertType('Composition', $composition = $food->compositions->last());
		$this->assertEquals(110, $composition->id);

		$ingredient = Ingredient::find(7);
		$this->assertType('ActiveCollection', $ingredient->compositions);
		$this->assertEquals(3, count($ingredient->compositions));
		$this->assertType('Composition', $composition = $ingredient->compositions->first());
		$this->assertEquals(102, $composition->id);
		$this->assertType('Composition', $composition = $ingredient->compositions->last());
		$this->assertEquals(110, $composition->id);
	}

	public function testReadAssociationHasManyViaThrough() {
		ActiveCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$programmer = Programmer::find(4);
		$this->assertType('ActiveCollection', $programmer->projects);
		$this->assertEquals(2, count($programmer->projects));
		$this->assertType('Project', $project = $programmer->projects->first());
		$this->assertEquals(1, $project->id);
		$this->assertType('Project', $project = $programmer->projects->last());
		$this->assertEquals(3, $project->id);

		$project = Project::find(1);
		$this->assertType('ActiveCollection', $project->programmers);
		$this->assertEquals(5, count($project->programmers));
		$this->assertType('Programmer', $programmer = $project->programmers->first());
		$this->assertEquals(1, $programmer->id);
		$this->assertType('Programmer', $programmer = $project->programmers->last());
		$this->assertEquals(7, $programmer->id);



		Inflector::$railsStyle = TRUE;

		$food = Food::find(3);
		$this->assertType('ActiveCollection', $food->ingredients);
		$this->assertEquals(6, count($food->ingredients));
		$this->assertType('Ingredient', $ingredient = $food->ingredients->first());
		$this->assertEquals(2, $ingredient->id);
		$this->assertType('Ingredient', $ingredient = $food->ingredients->last());
		$this->assertEquals(8, $ingredient->id);

		$ingredient = Ingredient::find(7);
		$this->assertType('ActiveCollection', $ingredient->foods);
		$this->assertEquals(3, count($ingredient->foods));
		$this->assertType('Food', $food = $ingredient->foods->first());
		$this->assertEquals(1, $food->id);
		$this->assertType('Food', $food = $ingredient->foods->last());
		$this->assertEquals(3, $food->id);
	}

	public function testReadAssociationHasManyAndBelongsTo() {
		ActiveCollection::$loadImmediately = TRUE;

		Inflector::$railsStyle = FALSE;

		$post = Post::find(4);
		$this->assertType('ActiveCollection', $post->tags);
		$this->assertEquals(2, count($post->tags));
		$this->assertType('Tag', $tag = $post->tags->first());
		$this->assertEquals(1, $tag->id);
		$this->assertType('Tag', $tag = $post->tags->last());
		$this->assertEquals(3, $tag->id);

		$tag = Tag::find(1);
		$this->assertType('ActiveCollection', $tag->posts);
		$this->assertEquals(5, count($tag->posts));
		$this->assertType('Post', $post = $tag->posts->first());
		$this->assertEquals(1, $post->id);
		$this->assertType('Post', $post = $tag->posts->last());
		$this->assertEquals(7, $post->id);



		Inflector::$railsStyle = TRUE;

		$album = Album::find(3);
		$this->assertType('ActiveCollection', $album->songs);
		$this->assertEquals(6, count($album->songs));
		$this->assertType('Song', $song = $album->songs->first());
		$this->assertEquals(2, $song->id);
		$this->assertType('Song', $song = $album->songs->last());
		$this->assertEquals(8, $song->id);

		$song = Song::find(7);
		$this->assertType('ActiveCollection', $song->albums);
		$this->assertEquals(3, count($song->albums));
		$this->assertType('Album', $album = $song->albums->first());
		$this->assertEquals(1, $album->id);
		$this->assertType('Album', $album = $song->albums->last());
		$this->assertEquals(3, $album->id);
	}



	/********************* Write association tests *********************/



	public function testWriteAssociationHasOne() {
		// read & save
		$student = Student::find(1);
		$this->assertEquals(1, $student->assignment->id);
		$student->assignment = Assignment::find(2);
		$this->assertEquals(2, $student->assignment->id);
		$student->save();

		// read updated & check
		$student = Student::find(1);
		$this->assertEquals(2, $student->assignment->id);
		$assignment = Assignment::find(2);
		$this->assertEquals(1, $assignment->student->id);

		// write wrong type
		$student = Student::find(1);
		$this->assertType('Assignment', $student->assignment);
		$this->setExpectedException('InvalidArgumentException');
		$student->assignment = new Student;
	}

	public function testWriteAssociationBelongsTo() {
		// read & save
		$assignment = Assignment::find(2);
		$this->assertEquals(2, $assignment->student->id);
		$assignment->student = Student::find(1);
		$this->assertEquals(1, $assignment->student->id);
		$assignment->save();

		// read updated & check
		$assignment = Assignment::find(2);
		$this->assertEquals(1, $assignment->student->id);
		$student = Student::find(1);
		$this->assertEquals(2, $student->assignment->id);

		// write wrong type
		$assignment = Assignment::find(2);
		$this->assertType('Student', $assignment->student);
		$this->setExpectedException('InvalidArgumentException');
		$assignment->student = new Assignment;
	}

	public function testWriteAssociationHasMany() {
		// read & save
		$programmer = Programmer::find(4);
		$this->assertEquals(2, count($programmer->tasks));
		$this->assertEquals(array(1003, 1009), $programmer->tasks->id);
		$programmer->tasks = Programmer::find(7)->tasks;
		$programmer->save();

		// read updated & check
		$programmer = Programmer::find(4);
		$this->assertEquals(3, count($programmer->tasks));
		$this->assertEquals(array(1004, 1006, 1010), $programmer->tasks->id);
		$task = Task::find(1006);
		$this->assertEquals(4, $task->programmer->id);

		// write wrong type
		$programmer = Programmer::find(4);
		$this->assertType('ActiveCollection', $programmer->tasks);
		$this->assertEquals('Task', $programmer->tasks->getItemType());
		$this->setExpectedException('InvalidArgumentException');
		$programmer->tasks = new Task;
	}

	public function testWriteAssociationHasManyAppend() {
		$this->markTestSkipped('NotImplemented');
		// read & save
		$programmer = Programmer::find(4);
		$this->assertEquals(2, count($programmer->tasks));
		$this->assertEquals(array(1003, 1009), $programmer->tasks->id);
		$programmer->tasks[] = Task::create(array('id' => 1011, 'name' => 'Task #1011'));
		$this->assertTrue($programmer->tasks->isDirty());
		$programmer->save();

		// read updated & check
		$programmer = Programmer::find(4);
		$this->assertEquals(3, count($programmer->tasks));
		$this->assertEquals(array(1003, 1009,1011), $programmer->tasks->id);
		$task = Task::find(1011);
		$this->assertEquals(4, $task->programmer);
		$this->assertEquals(NULL, $task->project);
	}

	public function testWriteAssociationHasManyViaThrough() {
		// read & save
		$programmer = Programmer::find(4);
		$this->assertEquals(2, count($programmer->projects));
		$this->assertEquals(array(1,3), $programmer->projects->id);
		// asserts projects 2 and 3 to programmer 4
		// (with all theirs tasks, existing programmer's projects [and tasks] will be unused or removed)
		$programmer->projects = Project::findAll(array(array('%n IN %l', 'id', array(2,3))));
		$programmer->save();

		// read updated & check
		$programmer = Programmer::find(4);
		$this->assertEquals(2, count($programmer->projects));
		$this->assertEquals(array(2,3), $programmer->projects->id);
		$project = Project::find(3);
		$this->assertEquals(array(4), $project->programmers->id);
		$this->assertEquals(array(4,4,4,4), $project->tasks->programmerId);

		// write wrong type
		$programmer = Programmer::find(4);
		$this->assertType('ActiveCollection', $programmer->projects);
		$this->assertEquals('Project', $programmer->projects->getItemType());
		$this->setExpectedException('InvalidArgumentException');
		$programmer->projects = new Project;
	}

	public function testWriteAssociationHasManyAppendViaThrough() {
		$this->markTestSkipped('NotImplemented');
		// read & save
		$programmer = Programmer::find(4);
		$this->assertEquals(2, count($programmer->projects));
		$this->assertEquals(array(1,3), $programmer->projects->id);
		$programmer->projects[] = Project::create(array('id' => 4, 'name' => 'Project #4'));
		$programmer->save();

		// read updated & check
		$programmer = Programmer::find(4);
		$this->assertEquals(3, count($programmer->projects));
		$this->assertEquals(array(1,3,4), $programmer->projects->id);
		$project = Project::find(4);
		$this->assertEquals(array(4), $project->programmer->id);
	}

	public function testWriteAssociationHasManyAndBelongsToMany() {
		// read & save
		$post = Post::find(4);
		$this->assertEquals(2, count($post->tags));
		$this->assertEquals(array(1,3), $post->tags->id);
		$post->tags = Tag::findAll(array(array('%n IN %l', 'id', array(2,3))));
		$post->save();

		// read updated & check
		$post = Post::find(4);
		$this->assertEquals(2, count($post->tags));
		$this->assertEquals(array(2,3), $post->tags->id);
		$tag = Tag::find(3);
		$this->assertEquals(1, count($tag->posts));
		$this->assertEquals(array(4), $tag->posts->id);

		// write wrong type
		$post = Post::find(4);
		$this->assertType('ActiveCollection', $post->tags);
		$this->assertEquals('Tag', $post->tags->getItemType());
		$this->setExpectedException('InvalidArgumentException');
		$post->tags = new Tag;
	}

	public function testWriteAssociationHasManyAndBelongsToManyAppend() {
		$this->markTestSkipped('NotImplemented');
		// read & save
		$post = Post::find(4);
		$this->assertEquals(2, count($post->tags));
		$this->assertEquals(array(1,3), $post->tags->id);
		$post->tags[] = Tag::create(array('id' => 4, 'name' => 'Tag #4'));
		$post->save();

		// read updated & check
		$post = Post::find(4);
		$this->assertEquals(3, count($post->tags));
		$this->assertEquals(array(1,3,4), $post->tags->id);
		$tag = Tag::find(4);
		$this->assertEquals(1, count($tag->posts));
		$this->assertEquals(array(4), $tag->posts->id);
	}



	/********************* Values *********************/



	function testGetValues() {
		$author = new Author;
		$cmp = array(
			'id' => NULL,
			'login' => NULL,
			'email' => NULL,
			'firstname' => NULL,
			'lastname' => NULL,
			'credit' => 0,
		);
		$this->assertEquals($cmp, (array) $author->values);


		$author = Author::findById(1);
		$cmp = array(
			'id' => 1,
			'login' => 'john007',
			'email' => 'john.doe@example.com',
			'firstname' => 'John',
			'lastname' => 'Doe',
			'credit' => 300,
		);
		$this->assertEquals($cmp, (array) $author->values);


		$values = array(
			'login' => 'johny007',
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
		);
		$cmp = $values + array('id' => 4, 'credit' => 0);
		$author = Author::create($values);
		$this->assertEquals($cmp, (array) $author->values);
	}



	function testSetValues() {
		$author = Author::find(2);
		$values = array(
			'id' => 1,
			'login' => 'johny007',
			'email' => 'johny.moe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Moe',
			'credit' => 123,
		);

		$origin = $author->values;
		$author->values = $values;
		$this->assertTrue($author->isDirty());
		$this->assertEquals($origin, (array) $author->originals);
		$this->assertEquals($values, (array) $author->changes);
	}




	/********************* Saving *********************/

	

	public function testSave() {
		$values = array(
			'login' => 'johny007',
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
		);

		$author = Author::create($values);
		$cmp = $values + array(
			'id' => 4,		// generated by autoincrement
			'credit' => 0,	// database default value
		);
		//$this->assertEquals("INSERT INTO [Authors] ([login], [email], [firstname], [lastname], [credit]) VALUES ('johny007', 'johny.doe@example.com', 'Johny', 'Doe', 0)", strip(dibi::$sql));
		$this->assertType('Author', $author);
		$this->assertFalse($author->isDirty());
		$this->assertTrue($author->isExistingRecord());
		$this->assertEquals($cmp, $author->values);
		$this->assertEquals(4, Author::count());

		$author->id = 55;
		$author->save();
		//$this->assertEquals("UPDATE [Authors] SET [id]=55 WHERE ([id] = 4)", strip(dibi::$sql));
		$this->assertEquals(55, $author->id);
		$this->assertFalse($author->isDirty());
		$this->assertTrue($author->isExistingRecord());
		
		$this->assertEquals(1, Author::count(55));

		$author = Author::find(55);
		$this->assertType('Author', $author);
		$this->assertFalse($author->isDirty());
		$this->assertTrue($author->isExistingRecord());
	}

	public function testCreateWithEmptyMandatoryField() {
		$values = array(
			//'id' => NULL, // primary autoincrement
			//'login' => NULL, // not null without default
			'email' => 'johny.doe@example.com',
			'firstname' => 'Johny',
			'lastname' => 'Doe',
			'credit' => 195,
		);

		$this->setExpectedException('ActiveRecordException');
		Author::create($values);
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

		$author = new Author($values);
		$this->assertType('Author', $author);
		$this->assertTrue($author->isNewRecord());
		$this->assertTrue($author->isDirty());
		$this->assertEquals($cmp, $author->values);
		$this->assertEquals($values, (array) $author->changes);
		$this->assertEquals(3, Author::count());

		$this->setExpectedException('ActiveRecordException');
		$author->save();
	}

	function testDestroy() {
		$author = Author::find(1);
		$author->destroy();
		$this->assertTrue($author->isFrozen());
		$this->assertTrue($author->isDeletedRecord());
		$this->assertEquals(0, Author::count(array('[id] = 1')));
	}

	function testDiscard() {
		$author = Author::find(1);
		$author->id = 5;
		$this->assertTrue($author->isDirty());
		$this->assertEquals(array('id' => 5), (array) $author->changes);
		$author->discard();
		$this->assertFalse($author->isDirty());
		$this->assertEquals(array(), (array) $author->changes);
	}
}