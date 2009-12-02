<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';

/**
 * Test class for Association.
 */
class AssociationTest extends ActiveRecordDatabaseTestCase {

	/** @var DibiConnection */
	protected $connection;


	public function setUp() {
		$this->connection = new DibiConnection($this->config);
		$this->connection->query("CREATE TABLE IF NOT EXISTS [As] ([id] INTEGER PRIMARY KEY UNIQUE)");
		$this->connection->query("CREATE TABLE IF NOT EXISTS [Bs] ([id] INTEGER PRIMARY KEY UNIQUE)");
		$this->connection->query("CREATE TABLE IF NOT EXISTS [Cs] ([id] INTEGER PRIMARY KEY UNIQUE)");
		$this->connection->query("CREATE TABLE IF NOT EXISTS [Ds] ([id] INTEGER PRIMARY KEY UNIQUE)");
		$this->connection->query("CREATE TABLE IF NOT EXISTS [Es] ([id] INTEGER PRIMARY KEY UNIQUE, [aId] INTEGER)");
		Mapper::addConnection($this->connection);
		CacheHelper::cleanCache();
	}

	public function tearDown() {
		Mapper::disconnect();
		CacheHelper::cleanCache();
	}

	public function testHasAnnotations() {
		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'hasOne'));
		$this->assertTrue(Annotations::has(new ReflectionClass('A'), 'hasMany'));
		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'belongsTo'));
		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'hasAndBelongsToMany'));

		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'hasOne'));
		$this->assertFalse(Annotations::has(new ReflectionClass('B'), 'hasMany'));
		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'belongsTo'));
		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'hasAndBelongsToMany'));

		$this->assertTrue(Annotations::has(new ReflectionClass('C'), 'hasOne'));
		$this->assertFalse(Annotations::has(new ReflectionClass('C'), 'hasMany'));
		$this->assertFalse(Annotations::has(new ReflectionClass('C'), 'belongsTo'));
		$this->assertFalse(Annotations::has(new ReflectionClass('C'), 'hasAndBelongsToMany'));

		$this->assertFalse(Annotations::has(new ReflectionClass('D'), 'hasOne'));
		$this->assertTrue(Annotations::has(new ReflectionClass('D'), 'hasMany'));
		$this->assertFalse(Annotations::has(new ReflectionClass('D'), 'belongsTo'));
		$this->assertFalse(Annotations::has(new ReflectionClass('D'), 'hasAndBelongsToMany'));
	}

	public function testGetAnnotations() {
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'hasOne'));
		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('A'), 'hasMany'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'belongsTo'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'hasAndBelongsToMany'));

		$this->assertEquals(array('ProjectManager', 'bossId' => '> Author'), (array) Annotations::get(new ReflectionClass('B'), 'hasOne'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('B'), 'hasMany'));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), Annotations::get(new ReflectionClass('B'), 'belongsTo'));
		$this->assertEquals('Categories', Annotations::get(new ReflectionClass('B'), 'hasAndBelongsToMany'));

		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('C'), 'hasOne'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('C'), 'hasMany'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('C'), 'belongsTo'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('C'), 'hasAndBelongsToMany'));

		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('D'), 'hasOne'));
		$this->assertEquals('ProjectManager', Annotations::get(new ReflectionClass('D'), 'hasMany'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('D'), 'belongsTo'));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('D'), 'hasAndBelongsToMany'));
	}

	public function testGetAllAnnotations() {
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'hasOne'));
		$this->assertEquals(array('Milestones'), Annotations::getAll(new ReflectionClass('A'), 'hasMany'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'belongsTo'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'hasAndBelongsToMany'));

		$this->assertEquals(array(new ArrayObject(array('ProjectManager', 'bossId' => '> Author'))), Annotations::getAll(new ReflectionClass('B'), 'hasOne'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('B'), 'hasMany'));
		$this->assertEquals(array(new ArrayObject(array('Portfolio', 'House'))), Annotations::getAll(new ReflectionClass('B'), 'belongsTo'));
		$this->assertEquals(array('Categories'), Annotations::getAll(new ReflectionClass('B'), 'hasAndBelongsToMany'));

		$this->assertEquals(array('Milestones'), Annotations::getAll(new ReflectionClass('C'), 'hasOne'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('C'), 'hasMany'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('C'), 'belongsTo'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('C'), 'hasAndBelongsToMany'));

		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('D'), 'hasOne'));
		$this->assertEquals(array(new ArrayObject(array('Payments', 'Pencils', 'Cabinets')), 'Cars', 'ProjectManager'), Annotations::getAll(new ReflectionClass('D'), 'hasMany'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('D'), 'belongsTo'));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('D'), 'hasAndBelongsToMany'));
	}

	public function testHasAnnotationsWithInheritance() {
		$this->markTestSkipped('Dedicnost je treba nejdrive vyresit ze strany Nette');

		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'hasOne', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('A'), 'hasMany', TRUE));
		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'belongsTo', TRUE));
		$this->assertFalse(Annotations::has(new ReflectionClass('A'), 'hasAndBelongsToMany', TRUE));

		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'hasOne', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'hasMany', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'belongsTo', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('B'), 'hasAndBelongsToMany', TRUE));

		$this->assertTrue(Annotations::has(new ReflectionClass('C'), 'hasOne', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('C'), 'hasMany', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('C'), 'belongsTo', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('C'), 'hasAndBelongsToMany', TRUE));

		$this->assertTrue(Annotations::has(new ReflectionClass('D'), 'hasOne', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('D'), 'hasMany', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('D'), 'belongsTo', TRUE));
		$this->assertTrue(Annotations::has(new ReflectionClass('D'), 'hasAndBelongsToMany', TRUE));
	}

	public function testGetAnnotationsWithInheritance() {
		$this->markTestSkipped('Dedicnost je treba nejdrive vyresit ze strany Nette');

		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'hasOne', TRUE));
		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('A'), 'hasMany', TRUE));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'belongsTo', TRUE));
		$this->assertEquals(NULL, Annotations::get(new ReflectionClass('A'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals(array('ProjectManager', 'bossId' => '> Author'), (array) Annotations::get(new ReflectionClass('B'), 'hasOne', TRUE));
		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('B'), 'hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), Annotations::get(new ReflectionClass('B'), 'belongsTo', TRUE));
		$this->assertEquals('Categories', Annotations::get(new ReflectionClass('B'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('C'), 'hasOne', TRUE));
		$this->assertEquals('Milestones', Annotations::get(new ReflectionClass('C'), 'hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), Annotations::get(new ReflectionClass('C'), 'belongsTo', TRUE));
		$this->assertEquals('Categories', Annotations::get(new ReflectionClass('C'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals(new ArrayObject(array('ProjectManager', 'bossId' => '> Author')), Annotations::get(new ReflectionClass('D'), 'hasOne', TRUE));
		$this->assertEquals('ProjectManager', Annotations::get(new ReflectionClass('D'), 'hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), Annotations::get(new ReflectionClass('D'), 'belongsTo', TRUE));
		$this->assertEquals('Categories', Annotations::get(new ReflectionClass('D'), 'hasAndBelongsToMany', TRUE));
	}

	public function testGetAllAnnotationsWithInheritance() {
		$this->markTestSkipped('Dedicnost je treba nejdrive vyresit ze strany Nette');

		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'hasOne', TRUE));
		$this->assertEquals(array('Milestones'), Annotations::getAll(new ReflectionClass('A'), 'hasMany', TRUE));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'belongsTo', TRUE));
		$this->assertEquals(array(), Annotations::getAll(new ReflectionClass('A'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals(array(new ArrayObject(array('ProjectManager', 'bossId' => '> Author'))), Annotations::getAll(new ReflectionClass('B'), 'hasOne', TRUE));
		$this->assertEquals(array('Milestones'), Annotations::getAll(new ReflectionClass('B'), 'hasMany', TRUE));
		$this->assertEquals(array(new ArrayObject(array('Portfolio', 'House'))), Annotations::getAll(new ReflectionClass('B'), 'belongsTo', TRUE));
		$this->assertEquals(array('Categories'), Annotations::getAll(new ReflectionClass('B'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals(array('Milestones', new ArrayObject(array('ProjectManager', 'bossId' => '> Author'))), Annotations::getAll(new ReflectionClass('C'), 'hasOne', TRUE));
		$this->assertEquals(array('Milestones'), Annotations::getAll(new ReflectionClass('C'), 'hasMany', TRUE));
		$this->assertEquals(array(new ArrayObject(array('Portfolio', 'House'))), Annotations::getAll(new ReflectionClass('C'), 'belongsTo', TRUE));
		$this->assertEquals(array('Categories'), Annotations::getAll(new ReflectionClass('C'), 'hasAndBelongsToMany', TRUE));

		$this->assertEquals(array(new ArrayObject(array('ProjectManager', 'bossId' => '> Author'))), Annotations::getAll(new ReflectionClass('D'), 'hasOne', TRUE));
		$this->assertEquals(array(new ArrayObject(array('Payments', 'Pencils', 'Cabinets')), 'Cars', 'ProjectManager', 'Milestones'), Annotations::getAll(new ReflectionClass('D'), 'hasMany', TRUE));
		$this->assertEquals(array(new ArrayObject(array('Portfolio', 'House'))), Annotations::getAll(new ReflectionClass('D'), 'belongsTo', TRUE));
		$this->assertEquals(array('Categories'), Annotations::getAll(new ReflectionClass('D'), 'hasAndBelongsToMany', TRUE));
	}

	public function testParseAssotiations() {
		Association::$inheritance = FALSE;

		$cmp = array(
			'belongsTo' => array('Portfolio', 'House'),
			'hasOne' => array('ProjectManager', 'bossId' => 'Author'),
			'hasMany' => array(),
			'hasAndBelongsToMany' => array('Categories'),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('B')));

		$cmp = array(
			'belongsTo' => array(),
			'hasOne' => array('Milestones'),
			'hasMany' => array(),
			'hasAndBelongsToMany' => array(),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('C')));

		$cmp = array(
			'belongsTo' => array(),
			'hasOne' => array(),
			'hasMany' => array('Payments', 'Pencils', 'Cabinets', 'Cars', 'ProjectManager'),
			'hasAndBelongsToMany' => array(),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('D')));
	}

	public function testParseAssotiationsWithInheritance() {
		$this->markTestSkipped('Dedicnost je treba nejdrive vyresit ze strany Nette');
		Association::$inheritance = TRUE;
		
		$cmp = array(
			'belongsTo' => array('Portfolio', 'House'),
			'hasOne' => array('ProjectManager', 'bossId' => 'Author'),
			'hasMany' => array('Milestones'),
			'hasAndBelongsToMany' => array('Categories'),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('B')));

		$cmp = array(
			'belongsTo' => array('Portfolio', 'House'),
			'hasOne' => array('Milestones', 'ProjectManager', 'bossId' => 'Author'),
			'hasMany' => array('Milestones'),
			'hasAndBelongsToMany' => array('Categories'),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('C')));

		$cmp = array(
			'belongsTo' => array('Portfolio', 'House'),
			'hasOne' => array('ProjectManager', 'bossId' => 'Author'),
			'hasMany' => array('Payments', 'Pencils', 'Cabinets', 'Cars', 'ProjectManager', 'Milestones'),
			'hasAndBelongsToMany' => array('Categories'),
		);

		$this->assertEquals($cmp, Association::parseAssotiations(new ReflectionClass('D')));
	}

	public function test__construct() {
		$this->markTestSkipped();
		$assoc = new Association(Association::HAS_ONE, 'C', $foreignTable);
	}


	public function testGetAssotiations() {
		$record = new E;
		$assc = Association::getAssotiations(new ReflectionClass('E'), 'Es');
		$this->assertType('array', $assc[Association::HAS_ONE]);
		$this->assertType('array', $assc[Association::HAS_MANY]);
		$this->assertType('array', $assc[Association::BELONGS_TO]);
		$this->assertType('array', $assc[Association::HAS_AND_BELONGS_TO_MANY]);

		$this->assertEquals(1, count($assc[Association::HAS_ONE]));
		$this->assertEquals(1, count($assc[Association::HAS_MANY]));
		$this->assertEquals(1, count($assc[Association::BELONGS_TO]));
		$this->assertEquals(1, count($assc[Association::HAS_AND_BELONGS_TO_MANY]));

		$this->assertTrue($assc[Association::HAS_ONE][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->localTable instanceof DibiTableInfo);

		$this->assertTrue($assc[Association::HAS_ONE][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->foreignTable instanceof DibiTableInfo);

		$this->markTestIncomplete('Nejdriv je potreba naimplementovat a otestovat konstruktor a samotnou tridu');
		$this->assertTrue($assc[Association::HAS_ONE][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->localColumn instanceof DibiColumnInfo);

		$this->assertTrue($assc[Association::HAS_ONE][0]->foreignColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->foreignColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->foreignColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->foreignColumn instanceof DibiColumnInfo);
	}
}




/**
 * @hasMany(Milestones)
 */
class A extends MockActiveRecord {}

/**
 * @hasOne(ProjectManager, bossId => Author)
 * @belongsTo(Portfolio, House)
 * @hasAndBelongsToMany(Categories)
 */
class B extends A {}

/**
 * @hasOne(Milestones)
 */
class C extends B {}

/**
 * @hasMany(Payments, Pencils, Cabinets)
 * @hasMany(Cars)
 * @hasMany(ProjectManager)
 */
class D extends B {}

/**
 * @hasOne(A)
 * @hasMany(B)
 * @belongsTo(C)
 * @hasAndBelongsToMany(D)
 */
class E extends MockActiveRecord {}