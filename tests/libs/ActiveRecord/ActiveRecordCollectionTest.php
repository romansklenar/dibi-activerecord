<?php

require_once __DIR__ . '/ActiveRecordDatabaseTestCase.php';


/**
 * Test class for ActiveRecordCollection.
 */
class ActiveRecordCollectionTest extends ActiveRecordDatabaseTestCase {

	public function testConstruct() {
		$mapper = new Mapper(new Office);
		$ds = $mapper->getConnection()->dataSource('SELECT * FROM [Offices]');
		$rs = new ActiveRecordCollection($ds, $mapper);
		
		$this->assertFalse($rs->isLoaded());
	}

	public function testGetItemType() {
		$offices = Office::objects();
		$this->assertEquals('Office', $offices->getItemType());
	}

	public function testIsLoaded() {
		$offices = Office::objects();
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects();
		$offices->count();
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects()->filter('[officeCode] > 2');
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects()->orderBy('officeCode', 'DESC');
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects();
		$offices->first();
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects();
		$offices->last();
		$this->assertFalse($offices->isLoaded());

		$offices = Office::objects()->reverse();
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects()->reverse();
		$offices->first();
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects()->reverse();
		$offices->last();
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects();
		$offices->clear();
		$this->assertTrue($offices->isLoaded()); // co je vice logicke? TRUE/FALSE ?

		$offices = Office::objects();
		$this->assertFalse($offices->isLoaded());
		$offices[0];
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects();
		$this->assertFalse($offices->isLoaded());
		$this->assertTrue(isset($offices[0]));
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects();
		$this->assertFalse($offices->isLoaded());
		$this->assertTrue($offices->contains(Office::find(1)));
		$this->assertTrue($offices->isLoaded());

		$offices = Office::objects();
		$this->assertFalse($offices->isLoaded());
		$offices->import(Office::find(1,2,3)->toArray());
		$this->assertTrue($offices->isLoaded());
	}

	public function testCount() {
		$offices = Office::objects();

		$this->assertType('object', $offices);
		$this->assertTrue($offices instanceof ActiveRecordCollection);
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(8, count($offices));
	}

	public function testFilter() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());

		$offices->filter('[officeCode] > 2');
		$this->assertEquals(6, $offices->count());
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices->filter('[officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);

		$offices = Office::objects()->filter('[officeCode] > 2')->filter('[officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);

		$offices = Office::objects()->filter('[officeCode] > 2 AND [officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);
		
		$offices = Office::objects()->filter(array('[officeCode] > 2', '[officeCode] < 6'));
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);

		$offices = Office::objects()->filter(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		);
		$this->assertEquals(3, count($offices));
		$this->assertEquals(3, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);
	}

	public function testFilterWithReverse() {
		$offices = Office::objects()->reverse();
		$this->assertEquals(8, $offices->count());

		$offices->filter('[officeCode] > 2');
		$this->assertEquals(6, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices->filter('[officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->filter('[officeCode] > 2')->filter('[officeCode] < 6')->reverse();
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->filter('[officeCode] > 2')->filter('[officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->filter('[officeCode] > 2')->reverse()->filter('[officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->filter('[officeCode] > 2 AND [officeCode] < 6')->reverse();
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->filter('[officeCode] > 2 AND [officeCode] < 6');
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->filter(array('[officeCode] > 2', '[officeCode] < 6'))->reverse();
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->filter(array('[officeCode] > 2', '[officeCode] < 6'));
		$this->assertEquals(3, $offices->count());
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->filter(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		)->reverse();
		$this->assertEquals(3, count($offices));
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->filter(
			array(
				array('%n > %i', 'position', 2),
				array('%n < %i', 'position', 6),
			)
		);
		$this->assertEquals(3, count($offices));
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(3, $offices->last()->officeCode);
	}

	public function testOrderBy() {
		$offices = Office::objects()->orderBy('officeCode', 'DESC');
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('officeCode', 'DESC')->orderBy('position', 'ASC');
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('[officeCode] DESC, [position] ASC');
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy(
			array('officeCode' => 'DESC', 'position' => 'ASC')
		);
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);
		
		$offices = Office::objects()->orderBy('officeCode', 'DESC')->applyLimit(5);
		$this->assertEquals(5, $offices->count());
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(4, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('officeCode', 'DESC')->applyLimit(5,2);
		$this->assertEquals(5, $offices->count());
		$this->assertEquals(6, $offices->first()->officeCode);
		$this->assertEquals(2, $offices->last()->officeCode);
	}

	public function testOrderByWithReverse() {
		$offices = Office::objects()->orderBy('officeCode', 'DESC')->reverse();
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('officeCode', 'DESC')->orderBy('position', 'ASC')->reverse();
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('[officeCode] DESC, [position] ASC')->reverse();
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->orderBy(
			array('officeCode' => 'DESC', 'position' => 'ASC')
		);
		$this->assertEquals(8, $offices->count());
		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('officeCode', 'DESC')->applyLimit(5)->reverse();
		$this->assertEquals(5, $offices->count());
		$this->assertEquals(4, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);

		$offices = Office::objects()->orderBy('officeCode', 'DESC')->applyLimit(5,2)->reverse();
		$this->assertEquals(5, $offices->count());
		$this->assertEquals(2, $offices->first()->officeCode);
		$this->assertEquals(6, $offices->last()->officeCode);
	}

	public function testApplyLimit() {
		$offices = Office::objects()->applyLimit(5);
		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertEquals(5, $offices->last()->officeCode);

		$offices = Office::objects()->applyLimit(5, 3);
		$this->assertEquals(4, $offices->first()->officeCode);
		$this->assertEquals(8, $offices->last()->officeCode);
	}

	public function testApplyLimitWithReverse() {
		$offices = Office::objects()->applyLimit(5)->reverse();
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);

		$offices = Office::objects()->applyLimit(5, 3)->reverse();
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(4, $offices->last()->officeCode);
		

		$offices = Office::objects()->reverse()->applyLimit(5);
		$this->assertEquals(5, $offices->first()->officeCode);
		$this->assertEquals(1, $offices->last()->officeCode);

		$offices = Office::objects()->reverse()->applyLimit(5, 3);
		$this->assertEquals(8, $offices->first()->officeCode);
		$this->assertEquals(4, $offices->last()->officeCode);
	}

	public function testContains() {
		$offices = Office::objects()->applyLimit(3);
		$this->assertEquals(3, $offices->count());
		$this->assertTrue($offices->contains(Office::find(1)));
		$this->assertTrue($offices->contains(Office::find(2)));
		$this->assertTrue($offices->contains(Office::find(3)));
		$this->assertFalse($offices->contains(Office::find(4)));
		$this->assertFalse($offices->contains(Office::find(5)));
	}

	public function testReverse() {
		$this->assertEquals(8, Office::count());
		$this->assertEquals(array_reverse(Office::objects()->toArray()), Office::objects()->reverse()->toArray());
	}

	public function testFirst() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());
		$this->assertFalse($offices->isLoaded());

		$this->assertEquals(1, $offices->first()->officeCode);
		$this->assertFalse($offices->isLoaded());
		$this->assertEquals('SELECT * FROM ( SELECT * FROM [Offices] ) t LIMIT 1', strip(dibi::$sql));
		$offices->remove($offices->first());
		$this->assertTrue($offices->isLoaded());

		$this->assertEquals(2, $offices->first()->officeCode);
		$offices->remove($offices->first());
		$this->assertEquals(3, $offices->first()->officeCode);
		$offices->remove($offices->first());
		$this->assertEquals(4, $offices->first()->officeCode);
		$offices->remove($offices->first());
		$this->assertEquals(5, $offices->first()->officeCode);
		$offices->remove($offices->first());
		$this->assertEquals(6, $offices->first()->officeCode);

		$offices->remove($offices->first());
		$offices->remove($offices->first());
		$offices->remove($offices->first());
		$this->assertEquals(0, $offices->count());
		$this->assertEquals(NULL, $offices->first());
	}

	public function testFirstWithReverse() {
		$offices = Office::objects()->reverse();
		$this->assertEquals(8, $offices->count());

		$this->assertEquals(8, $offices->first()->officeCode);
		unset($offices[0]);
		$this->assertEquals(7, $offices->first()->officeCode);
		unset($offices[0]);
		$this->assertEquals(6, $offices->first()->officeCode);
		unset($offices[0]);
		$this->assertEquals(5, $offices->first()->officeCode);
		unset($offices[0]);
		$this->assertEquals(4, $offices->first()->officeCode);
		unset($offices[0]);
		$this->assertEquals(3, $offices->first()->officeCode);

		unset($offices[0], $offices[0], $offices[0]);
		$this->assertEquals(0, $offices->count());
		$this->assertEquals(NULL, $offices->first());
	}

	public function testLast() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());

		$this->assertEquals(8, $offices->last()->officeCode);
		$this->assertFalse($offices->isLoaded());
		$this->assertEquals('SELECT * FROM ( SELECT * FROM [Offices] ) t LIMIT 1 OFFSET 7', strip(dibi::$sql));
		$offices->remove($offices->last());
		$this->assertTrue($offices->isLoaded());
		
		$this->assertEquals(7, $offices->last()->officeCode);
		$offices->remove($offices->last());
		$this->assertEquals(6, $offices->last()->officeCode);
		$offices->remove($offices->last());
		$this->assertEquals(5, $offices->last()->officeCode);
		$offices->remove($offices->last());
		$this->assertEquals(4, $offices->last()->officeCode);
		$offices->remove($offices->last());
		$this->assertEquals(3, $offices->last()->officeCode);

		$offices->remove($offices->last());
		$offices->remove($offices->last());
		$offices->remove($offices->last());
		$this->assertEquals(0, $offices->count());
		$this->assertEquals(NULL, $offices->last());
	}

	public function testLastWithReverse() {
		$offices = Office::objects()->reverse();
		$this->assertEquals(8, $offices->count());

		$this->assertEquals(1, $offices->last()->officeCode);
		unset($offices[7]);
		$this->assertEquals(2, $offices->last()->officeCode);
		unset($offices[6]);
		$this->assertEquals(3, $offices->last()->officeCode);
		unset($offices[5]);
		$this->assertEquals(4, $offices->last()->officeCode);
		unset($offices[4]);
		$this->assertEquals(5, $offices->last()->officeCode);
		unset($offices[3]);
		$this->assertEquals(6, $offices->last()->officeCode);

		unset($offices[2], $offices[1], $offices[0]);
		$this->assertEquals(0, $offices->count());
		$this->assertEquals(NULL, $offices->last());
	}

	public function testAppend() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());

		$offices->append(new Office);
		$this->assertEquals(9, $offices->count());

		$this->setExpectedException('InvalidArgumentException');
		$offices->append(new Employee);
	}

	public function testAsort() {
		$offices = Office::objects();
		$offices->asort();
		$this->assertEquals(8, $offices->count());
	}

	public function testClear() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());
		$offices->clear();
		$this->assertEquals(0, $offices->count());
	}

	public function testImport() {
		$offices = Office::objects();
		$this->assertEquals(8, $offices->count());
		$offices->import(Office::find(1,2,3)->toArray());
		$this->assertEquals(3, $offices->count());
	}

	public function testOffsetSet() {
		$this->setExpectedException('InvalidArgumentException');
		$offices[] = new Employee;

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

}