<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';


/**
 * Test class for ActiveRecordCollection.
 */
class ActiveRecordCollectionTest extends ActiveRecordDatabaseTestCase {

	/** @var ActiveRecordCollection */
	public $object;

	public function setUp() {
		parent::setUp();
		$this->object = self::createCollection();
	}

	private static function createCollection() {
		$ds = ActiveMapper::getConnection()->dataSource('Offices');
		return new ActiveRecordCollection($ds, 'Office');
	}

	public function testConstruct() {
		$this->assertEquals('SELECT * FROM [Offices]', strip((string) ActiveMapper::getConnection()->dataSource('Offices')));

		ActiveRecordCollection::$loadImmediately = FALSE;
		$collection = self::createCollection();
		$this->assertFalse($collection->isLoaded());
		$collection->load();
		$this->assertTrue($collection->isLoaded());
		$this->assertEquals('SELECT * FROM [Offices]', strip(dibi::$sql));

		ActiveRecordCollection::$loadImmediately = TRUE;
		$collection = self::createCollection();
		$this->assertTrue($collection->isLoaded());
		$this->assertEquals('SELECT * FROM [Offices]', strip(dibi::$sql));
	}

	public function testGetItemType() {
		$this->assertEquals('Office', $this->object->getItemType());
	}

	public function testIsLoaded() {
		$collection = self::createCollection();
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->count();
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->filter('[officeCode] > 2');
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC');
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->first();
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->last();
		$this->assertFalse($collection->isLoaded());

		$collection = self::createCollection();
		$collection->reverse();
		$this->assertTrue($collection->isLoaded());

		$collection = self::createCollection();
		$collection->reverse();
		$collection->first();
		$this->assertTrue($collection->isLoaded());

		$collection = self::createCollection();
		$collection->reverse();
		$collection->last();
		$this->assertTrue($collection->isLoaded());

		$collection = self::createCollection();
		$collection->clear();
		$this->assertTrue($collection->isLoaded()); // co je vice logicke? TRUE/FALSE ?

		$collection = self::createCollection();
		$collection[0];
		$this->assertTrue($collection->isLoaded());

		$collection = self::createCollection();
		$this->assertTrue(isset($collection[0]));
		$this->assertTrue($collection->isLoaded());

		$this->markTestIncomplete();

		$collection = self::createCollection();
		$this->assertTrue($collection->contains(Office::find(1)));
		$this->assertTrue($collection->isLoaded());

		$collection = self::createCollection();
		$collection->import(Office::find(1,2,3)->toArray());
		$this->assertTrue($collection->isLoaded());
	}

	public function testCount() {
		$this->assertEquals(8, $this->object->count());
		$this->assertEquals(8, count($this->object));
	}

	public function testFilter() {
		$collection = self::createCollection();
		$this->assertEquals(8, $collection->count());

		$collection->filter('[officeCode] > 2');
		$this->assertEquals(6, $collection->count());
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);

		$collection->filter('[officeCode] < 6');
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter('[officeCode] > 2')->filter('[officeCode] < 6');
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter('[officeCode] > 2 AND [officeCode] < 6');
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter(array('[officeCode] > 2', '[officeCode] < 6'));
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		);
		$this->assertEquals(3, count($collection));
		$this->assertEquals(3, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$this->markTestIncomplete('NotImplemented');

		$collection = self::createCollection();
		$collection->filter(array('officeCode' => 2, 'officeCode' => 6));
		$this->assertEquals(2, $collection->count());
		$this->assertEquals(2, $collection->first()->officeCode);
		$this->assertEquals(6, $collection->last()->officeCode);
	}

	public function testFilterWithReverse() {
		$collection = self::createCollection();
		$collection->reverse();
		$this->assertEquals(8, $collection->count());

		$collection->filter('[officeCode] > 2');
		$this->assertEquals(6, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection->filter('[officeCode] < 6');
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter('[officeCode] > 2')->filter('[officeCode] < 6')->reverse();
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter('[officeCode] > 2 AND [officeCode] < 6')->reverse();
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter(array('[officeCode] > 2', '[officeCode] < 6'))->reverse();
		$this->assertEquals(3, $collection->count());
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->filter(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		)->reverse();
		$this->assertEquals(3, count($collection));
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(3, $collection->last()->officeCode);
		
		$this->markTestIncomplete('NotImplemented');

		$collection = self::createCollection();
		$collection->filter(array('officeCode' => 2, 'officeCode' => 6))->reverse();
		$this->assertEquals(2, $collection->count());
		$this->assertEquals(6, $collection->first()->officeCode);
		$this->assertEquals(2, $collection->last()->officeCode);
	}

	public function testOrderBy() {
		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC');
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(1, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->orderBy('position', 'ASC');
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(1, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('[officeCode] DESC, [position] ASC');
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(1, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy(
			array('officeCode' => 'DESC', 'position' => 'ASC')
		);
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(1, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->applyLimit(5);
		$this->assertEquals(5, $collection->count());
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(4, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->applyLimit(5,2);
		$this->assertEquals(5, $collection->count());
		$this->assertEquals(6, $collection->first()->officeCode);
		$this->assertEquals(2, $collection->last()->officeCode);
	}

	public function testOrderByWithReverse() {
		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->reverse();
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(1, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->orderBy('position', 'ASC')->reverse();
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(1, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('[officeCode] DESC, [position] ASC')->reverse();
		$this->assertEquals(8, $collection->count());
		$this->assertEquals(1, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->applyLimit(5)->reverse();
		$this->assertEquals(5, $collection->count());
		$this->assertEquals(4, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->orderBy('officeCode', 'DESC')->applyLimit(5,2)->reverse();
		$this->assertEquals(5, $collection->count());
		$this->assertEquals(2, $collection->first()->officeCode);
		$this->assertEquals(6, $collection->last()->officeCode);
	}

	public function testApplyLimit() {
		$collection = self::createCollection();
		$collection->applyLimit(5);
		$this->assertEquals(1, $collection->first()->officeCode);
		$this->assertEquals(5, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->applyLimit(5, 3);
		$this->assertEquals(4, $collection->first()->officeCode);
		$this->assertEquals(8, $collection->last()->officeCode);
	}

	public function testApplyLimitWithReverse() {
		$collection = self::createCollection();
		$collection->applyLimit(5)->reverse();
		$this->assertEquals(5, $collection->first()->officeCode);
		$this->assertEquals(1, $collection->last()->officeCode);

		$collection = self::createCollection();
		$collection->applyLimit(5, 3)->reverse();
		$this->assertEquals(8, $collection->first()->officeCode);
		$this->assertEquals(4, $collection->last()->officeCode);
	}

	public function testContains() {
		$this->object->applyLimit(3);
		$this->assertEquals(3, $this->object->count());
		$this->assertTrue($this->object->contains(Office::find(1)));
		$this->assertTrue($this->object->contains(Office::find(2)));
		$this->assertTrue($this->object->contains(Office::find(3)));
		$this->assertFalse($this->object->contains(Office::find(4)));
		$this->assertFalse($this->object->contains(Office::find(5)));
	}

	public function testReverse() {
		$this->assertEquals(8, Office::count());
		$this->assertEquals(array_reverse(Office::objects()->getArrayCopy()), Office::objects()->reverse()->getArrayCopy());
	}

	public function testFirst() {
		$collection = self::createCollection();
		$this->assertEquals(8, $collection->count());
		$this->assertFalse($collection->isLoaded());

		$this->assertEquals(1, $collection->first()->officeCode);
		$this->assertFalse($collection->isLoaded());
		$this->assertEquals('SELECT * FROM ( SELECT * FROM [Offices] ) t LIMIT 1', strip(dibi::$sql));
		$collection->remove($collection->first());
		$this->assertTrue($collection->isLoaded());

		$this->assertEquals(2, $collection->first()->officeCode);
		$collection->remove($collection->first());
		$this->assertEquals(3, $collection->first()->officeCode);
		$collection->remove($collection->first());
		$this->assertEquals(4, $collection->first()->officeCode);
		$collection->remove($collection->first());
		$this->assertEquals(5, $collection->first()->officeCode);
		$collection->remove($collection->first());
		$this->assertEquals(6, $collection->first()->officeCode);

		$collection->remove($collection->first());
		$collection->remove($collection->first());
		$collection->remove($collection->first());
		$this->assertEquals(0, $collection->count());
		$this->assertEquals(NULL, $collection->first());
	}

	public function testFirstWithReverse() {
		$collection = self::createCollection();
		$collection->reverse();
		$this->assertEquals(8, $collection->count());

		$this->assertEquals(8, $collection->first()->officeCode);
		unset($collection[0]);
		$this->assertEquals(7, $collection->first()->officeCode);
		unset($collection[0]);
		$this->assertEquals(6, $collection->first()->officeCode);
		unset($collection[0]);
		$this->assertEquals(5, $collection->first()->officeCode);
		unset($collection[0]);
		$this->assertEquals(4, $collection->first()->officeCode);
		unset($collection[0]);
		$this->assertEquals(3, $collection->first()->officeCode);

		unset($collection[0], $collection[0], $collection[0]);
		$this->assertEquals(0, $collection->count());
		$this->assertEquals(NULL, $collection->first());
	}

	public function testLast() {
		$collection = self::createCollection();
		$this->assertEquals(8, $collection->count());

		$this->assertEquals(8, $collection->last()->officeCode);
		$this->assertFalse($collection->isLoaded());
		$this->assertEquals('SELECT * FROM ( SELECT * FROM [Offices] ) t LIMIT 1 OFFSET 7', strip(dibi::$sql));
		$collection->remove($collection->last());
		$this->assertTrue($collection->isLoaded());

		$this->assertEquals(7, $collection->last()->officeCode);
		$collection->remove($collection->last());
		$this->assertEquals(6, $collection->last()->officeCode);
		$collection->remove($collection->last());
		$this->assertEquals(5, $collection->last()->officeCode);
		$collection->remove($collection->last());
		$this->assertEquals(4, $collection->last()->officeCode);
		$collection->remove($collection->last());
		$this->assertEquals(3, $collection->last()->officeCode);

		$collection->remove($collection->last());
		$collection->remove($collection->last());
		$collection->remove($collection->last());
		$this->assertEquals(0, $collection->count());
		$this->assertEquals(NULL, $collection->last());
	}

	public function testLastWithReverse() {
		$collection = self::createCollection();
		$collection->reverse();
		$this->assertEquals(8, $collection->count());

		$this->assertEquals(1, $collection->last()->officeCode);
		unset($collection[7]);
		$this->assertEquals(2, $collection->last()->officeCode);
		unset($collection[6]);
		$this->assertEquals(3, $collection->last()->officeCode);
		unset($collection[5]);
		$this->assertEquals(4, $collection->last()->officeCode);
		unset($collection[4]);
		$this->assertEquals(5, $collection->last()->officeCode);
		unset($collection[3]);
		$this->assertEquals(6, $collection->last()->officeCode);

		unset($collection[2], $collection[1], $collection[0]);
		$this->assertEquals(0, $collection->count());
		$this->assertEquals(NULL, $collection->last());
	}

	public function testAppend() {
		$this->assertEquals(8, $this->object->count());
		$this->object->append(new Office);
		$this->assertEquals(9, $this->object->count());
	}

	public function testAsort() {
		$this->object->asort();
		$this->assertEquals(8, $this->object->count());
	}

	public function testClear() {
		$this->assertEquals(8, $this->object->count());
		$this->object->clear();
		$this->assertEquals(0, $this->object->count());
	}

	public function testImport() {
		$this->assertEquals(8, $this->object->count());
		$this->object->import(Office::find(1,2,3)->getArrayCopy());
		$this->assertEquals(3, $this->object->count());
	}

	public function testAppendWrongDataType() {
		$this->setExpectedException('InvalidArgumentException');
		$this->object->append(new Employee);
	}

	public function testOffsetSet() {
		$this->assertEquals(8, $this->object->count());
		$this->object[] = new Office;
		$this->assertEquals(9, $this->object->count());

		$this->markTestIncomplete();
	}

	public function testOffsetSetWrongDataType() {
		$this->setExpectedException('InvalidArgumentException');
		$this->object[] = new Employee;

		$this->markTestIncomplete();
	}

	public function offsetExists() {
		$this->markTestIncomplete();
	}

	public function testOffsetGet() {
		$this->markTestIncomplete();
	}

	public function testOffsetUnset() {
		$this->markTestIncomplete();
	}

	public function testMassGetter() {
		$collection = self::createCollection();
		$arr = $collection->officeCode;
		$cmp = array('1','2','3','4','5','6','7','8');
		$this->assertEquals($cmp, $arr);
	}

	public function testMassSetter() {
		$collection = self::createCollection();
		$collection->officeCode = 3;

		foreach ($collection as $record)
			$this->assertEquals(3, $record->officeCode);
	}

	public function testGetPairs() {
		$collection = self::createCollection();
		$pairs = $collection->getPairs('officeCode', 'city');

		$cmp = array(
			1 => "San Francisco",
			2 => "Boston",
			3 => "NYC",
			4 => "Paris",
			5 => "Tokyo",
			6 => "Sydney",
			7 => "London",
			8 => "Ostrava",
		);
		$this->assertEquals($cmp, $pairs);
	}

}