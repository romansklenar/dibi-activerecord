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

	public function _testBenchmarkActiveRecord() {

		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');
		$connection->loadFile(APP_DIR . '/models/consumers.data.sql');
		Mapper::addConnection($connection);
		CacheHelper::cleanCache();

		// kalibrace
		Debug::$showLocation = FALSE;
		$consumers = Consumer::find(1,2,3,4,5)->toArray();

		Debug::timer('1');
		Debug::memory('1');
		$consumer = Consumer::findOne();
		$m = Debug::memory('1');
		$t = Debug::timer('1');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('10');
		Debug::memory('10');
		$consumers = Consumer::find(1,2,3,4,5,6,7,8,9,10);
		$consumers->load();
		$m = Debug::memory('10');
		$t = Debug::timer('10');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('100');
		Debug::memory('100');
		$consumers = Consumer::find()->applyLimit(100);
		$consumers->load();
		$m = Debug::memory('100');
		$t = Debug::timer('100');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('1000');
		Debug::memory('1000');
		$consumers = Consumer::find()->applyLimit(1000);
		$consumers->load();
		$m = Debug::memory('1000');
		$t = Debug::timer('1000');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('50000');
		Debug::memory('50000');
		$consumers = Consumer::find();
		$consumers->load();
		$m = Debug::memory('50000');
		$t = Debug::timer('50000');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		// clean up
		Mapper::disconnect();
		CacheHelper::cleanCache();
	}


	public function _testBenchmarkDibi() {

		Debug::$showLocation = FALSE;
		dibi::connect($this->config);
		dibi::loadFile(APP_DIR . '/models/consumers.structure.sql');
		dibi::loadFile(APP_DIR . '/models/consumers.data.sql');

		// kalibrace
		$res = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->load();


		Debug::timer('1');
		Debug::memory('1');
		$row = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->load();
		$m = Debug::memory('1');
		$t = Debug::timer('1');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('10');
		Debug::memory('10');
		$rows = dibi::dataSource('Consumers')->applyLimit(10)->getResult()->detectTypes()->fetchAll();
		$m = Debug::memory('10');
		$t = Debug::timer('10');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('100');
		Debug::memory('100');
		$rows = dibi::dataSource('Consumers')->applyLimit(100)->getResult()->detectTypes()->fetchAll();
		$m = Debug::memory('100');
		$t = Debug::timer('100');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('1000');
		Debug::memory('1000');
		$rows = dibi::dataSource('Consumers')->applyLimit(1000)->getResult()->detectTypes()->fetchAll();
		$m = Debug::memory('1000');
		$t = Debug::timer('1000');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		Debug::timer('50000');
		Debug::memory('50000');
		$rows = dibi::dataSource('Consumers')->applyLimit(50000)->getResult()->detectTypes()->fetchAll();
		$m = Debug::memory('50000');
		$t = Debug::timer('50000');
		Debug::dump(self::formatMemory($m));
		Debug::dump(self::formatTime($t));

		dibi::disconnect();
	}


	private static function formatMemory($bytes) {
		return number_format($bytes / 1024, 1, ',', ' ') . ' kB';
	}

	private static function formatTime($seconds) {
		return number_format($seconds * 1000, 1, ',', ' ') . ' ms';
	}
}