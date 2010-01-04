<?php

require_once 'PHPUnit/Framework.php';

/**
 * ActiveRecord benchmark.
 */
class BenchmarkTest extends PHPUnit_Framework_TestCase {

	/** @var array  database configuration settings */
	protected $config = array(
		'driver' => 'sqlite3',
		'database' => ':memory:',
	);

	public function testBenchmarkActiveRecord() {

		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');
		$connection->loadFile(APP_DIR . '/models/consumers.data.sql');
		Mapper::addConnection($connection);
		CacheHelper::cleanCache();

		// kalibrace
		dump('--- ActiveRecord ---');
		$showLocation = FALSE;
		$consumers = Consumer::find(1,2,3,4,5)->toArray();

		timer('1');
		memory('1');
		$consumer = Consumer::findOne();
		$m = memory('1');
		$t = timer('1');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('10');
		memory('10');
		$consumers = Consumer::find(1,2,3,4,5,6,7,8,9,10);
		$consumers->load();
		$m = memory('10');
		$t = timer('10');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('100');
		memory('100');
		$consumers = Consumer::find()->applyLimit(100);
		$consumers->load();
		$m = memory('100');
		$t = timer('100');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('1000');
		memory('1000');
		$consumers = Consumer::find()->applyLimit(1000);
		$consumers->load();
		$m = memory('1000');
		$t = timer('1000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		$this->markTestIncomplete();

		timer('50000');
		memory('50000');
		$consumers = Consumer::find();
		$consumers->load();
		$m = memory('50000');
		$t = timer('50000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		// clean up
		Mapper::disconnect();
		CacheHelper::cleanCache();
	}


	public function testBenchmarkDibi() {

		$showLocation = FALSE;
		dibi::connect($this->config);
		dibi::loadFile(APP_DIR . '/models/consumers.structure.sql');
		dibi::loadFile(APP_DIR . '/models/consumers.data.sql');

		// kalibrace
		dump('--- Dibi ---');
		$res = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->fetch();


		timer('1');
		memory('1');
		$row = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->fetch();
		$m = memory('1');
		$t = timer('1');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('10');
		memory('10');
		$rows = dibi::dataSource('Consumers')->applyLimit(10)->getResult()->detectTypes()->fetchAll();
		$m = memory('10');
		$t = timer('10');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('100');
		memory('100');
		$rows = dibi::dataSource('Consumers')->applyLimit(100)->getResult()->detectTypes()->fetchAll();
		$m = memory('100');
		$t = timer('100');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('1000');
		memory('1000');
		$rows = dibi::dataSource('Consumers')->applyLimit(1000)->getResult()->detectTypes()->fetchAll();
		$m = memory('1000');
		$t = timer('1000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		$this->markTestIncomplete();

		timer('50000');
		memory('50000');
		$rows = dibi::dataSource('Consumers')->applyLimit(50000)->getResult()->detectTypes()->fetchAll();
		$m = memory('50000');
		$t = timer('50000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		dibi::disconnect();
	}


	private static function formatMemory($bytes) {
		return number_format($bytes / 1024, 1, ',', ' ') . ' kB';
	}

	private static function formatTime($seconds) {
		return number_format($seconds * 1000, 1, ',', ' ') . ' ms';
	}
}