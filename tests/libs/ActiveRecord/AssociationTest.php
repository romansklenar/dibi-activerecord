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

	public function testHasAnnotation() {
		$a = new A;
		$b = new B;
		$c = new C;
		$d = new D;

		$this->assertFalse($a->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($a->getReflection()->hasAnnotation('hasMany'));
		$this->assertFalse($a->getReflection()->hasAnnotation('belongsTo'));
		$this->assertFalse($a->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertTrue($b->getReflection()->hasAnnotation('hasOne'));
		$this->assertFalse($b->getReflection()->hasAnnotation('hasMany'));
		$this->assertTrue($b->getReflection()->hasAnnotation('belongsTo'));
		$this->assertTrue($b->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertTrue($c->getReflection()->hasAnnotation('hasOne'));
		$this->assertFalse($c->getReflection()->hasAnnotation('hasMany'));
		$this->assertFalse($c->getReflection()->hasAnnotation('belongsTo'));
		$this->assertFalse($c->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertFalse($d->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($d->getReflection()->hasAnnotation('hasMany'));
		$this->assertFalse($d->getReflection()->hasAnnotation('belongsTo'));
		$this->assertFalse($d->getReflection()->hasAnnotation('hasAndBelongsToMany'));
	}

	public function testGetAnnotation() {
		$a = new A;
		$b = new B;
		$c = new C;
		$d = new D;

		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('hasOne'));
		$this->assertEquals(new HasManyAnnotation(array('Milestones')), $a->getReflection()->getAnnotation('hasMany'));
		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('belongsTo'));
		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('hasAndBelongsToMany'));

		$this->assertEquals(new HasOneAnnotation(array('ProjectManager', 'bossId' => '> Author')), $b->getReflection()->getAnnotation('hasOne'));
		$this->assertEquals(NULL, $b->getReflection()->getAnnotation('hasMany'));
		$this->assertEquals(new BelongsToAnnotation(array('Portfolio', 'House')), $b->getReflection()->getAnnotation('belongsTo'));
		$this->assertEquals(new HasAndBelongsToManyAnnotation(array('Categories')), $b->getReflection()->getAnnotation('hasAndBelongsToMany'));

		$this->assertEquals(new HasOneAnnotation(array('Milestones')), $c->getReflection()->getAnnotation('hasOne'));
		$this->assertEquals(NULL, $c->getReflection()->getAnnotation('hasMany'));
		$this->assertEquals(NULL, $c->getReflection()->getAnnotation('belongsTo'));
		$this->assertEquals(NULL, $c->getReflection()->getAnnotation('hasAndBelongsToMany'));

		$this->assertEquals(NULL, $d->getReflection()->getAnnotation('hasOne'));
		$this->assertEquals(new HasManyAnnotation(array('ProjectManager')), $d->getReflection()->getAnnotation('hasMany'));
		$this->assertEquals(NULL, $d->getReflection()->getAnnotation('belongsTo'));
		$this->assertEquals(NULL, $d->getReflection()->getAnnotation('hasAndBelongsToMany'));
	}

	public function testGetAllAnnotations() {
		$this->markTestSkipped('Je treba nejdrive vyresit ze strany Nette');
		
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
		
		$a = new A;
		$b = new B;
		$c = new C;
		$d = new D;

		$this->assertFalse($a->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($a->getReflection()->hasAnnotation('hasMany'));
		$this->assertFalse($a->getReflection()->hasAnnotation('belongsTo'));
		$this->assertFalse($a->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertTrue($b->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($b->getReflection()->hasAnnotation('hasMany'));
		$this->assertTrue($b->getReflection()->hasAnnotation('belongsTo'));
		$this->assertTrue($b->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertTrue($c->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($c->getReflection()->hasAnnotation('hasMany'));
		$this->assertTrue($c->getReflection()->hasAnnotation('belongsTo'));
		$this->assertTrue($c->getReflection()->hasAnnotation('hasAndBelongsToMany'));

		$this->assertTrue($d->getReflection()->hasAnnotation('hasOne'));
		$this->assertTrue($d->getReflection()->hasAnnotation('hasMany'));
		$this->assertTrue($d->getReflection()->hasAnnotation('belongsTo'));
		$this->assertTrue($d->getReflection()->hasAnnotation('hasAndBelongsToMany'));
	}

	public function testGetAnnotationsWithInheritance() {
		$this->markTestSkipped('Dedicnost je treba nejdrive vyresit ze strany Nette');

		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('hasOne', TRUE));
		$this->assertEquals('Milestones', $a->getReflection()->getAnnotation('hasMany', TRUE));
		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('belongsTo', TRUE));
		$this->assertEquals(NULL, $a->getReflection()->getAnnotation('hasAndBelongsToMany', TRUE));

		$this->assertEquals(array('ProjectManager', 'bossId' => '> Author'), (array) $b->getReflection()->getAnnotation('hasOne', TRUE));
		$this->assertEquals('Milestones', $b->getReflection()->getAnnotation('hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), $b->getReflection()->getAnnotation('belongsTo', TRUE));
		$this->assertEquals('Categories', $b->getReflection()->getAnnotation('hasAndBelongsToMany', TRUE));

		$this->assertEquals('Milestones', $c->getReflection()->getAnnotation('hasOne', TRUE));
		$this->assertEquals('Milestones', $c->getReflection()->getAnnotation('hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), $c->getReflection()->getAnnotation('belongsTo', TRUE));
		$this->assertEquals('Categories', $c->getReflection()->getAnnotation('hasAndBelongsToMany', TRUE));

		$this->assertEquals(new ArrayObject(array('ProjectManager', 'bossId' => '> Author')), $d->getReflection()->getAnnotation('hasOne', TRUE));
		$this->assertEquals('ProjectManager', $d->getReflection()->getAnnotation('hasMany', TRUE));
		$this->assertEquals(new ArrayObject(array('Portfolio', 'House')), $d->getReflection()->getAnnotation('belongsTo', TRUE));
		$this->assertEquals('Categories', $d->getReflection()->getAnnotation('hasAndBelongsToMany', TRUE));
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

	public function testGetAssotiations() {
		$a = new A;
		$b = new B;
		$c = new C;
		$d = new D;
		$e = new E;

		$assc = Association::getAssotiations($e->getReflection(), 'Es');
		$this->assertType('array', $assc[Association::HAS_ONE]);
		$this->assertType('array', $assc[Association::HAS_MANY]);
		$this->assertType('array', $assc[Association::BELONGS_TO]);
		$this->assertType('array', $assc[Association::HAS_AND_BELONGS_TO_MANY]);

		$this->assertEquals(2, count($assc[Association::HAS_ONE]));
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

	public function testGetAssotiations2() {
		$a = new A;
		$b = new B;
		$c = new C;
		$d = new D;
		$e = new E;

		$assc = Association::getAssotiations($e->getReflection(), 'Es');
		$this->assertType('array', $assc[Association::HAS_ONE]);
		$this->assertType('array', $assc[Association::HAS_MANY]);
		$this->assertType('array', $assc[Association::BELONGS_TO]);
		$this->assertType('array', $assc[Association::HAS_AND_BELONGS_TO_MANY]);

		$this->assertEquals(2, count($assc[Association::HAS_ONE]));
		$this->assertEquals(1, count($assc[Association::HAS_MANY]));
		$this->assertEquals(1, count($assc[Association::BELONGS_TO]));
		$this->assertEquals(1, count($assc[Association::HAS_AND_BELONGS_TO_MANY]));


		$this->assertTrue($assc[Association::HAS_ONE][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_ONE][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_ONE][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_ONE][0]->foreignColumn instanceof DibiColumnInfo);

		$this->assertTrue($assc[Association::HAS_MANY][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->localColumn instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_MANY][0]->foreignColumn instanceof DibiColumnInfo);

		$this->assertTrue($assc[Association::BELONGS_TO][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::BELONGS_TO][0]->foreignColumn instanceof DibiColumnInfo);

		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->localTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->foreignTable instanceof DibiTableInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->localColumn instanceof DibiColumnInfo);
		$this->assertTrue($assc[Association::HAS_AND_BELONGS_TO_MANY][0]->foreignColumn instanceof DibiColumnInfo);
	}
	
	public function test__construct() {
		$this->markTestSkipped();
		$assoc = new Association(Association::HAS_ONE, 'C', $foreignTable);
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
 * @hasOne(B)
 * @hasMany(B)
 * @belongsTo(C)
 * @hasAndBelongsToMany(D)
 */
class E extends MockActiveRecord {}