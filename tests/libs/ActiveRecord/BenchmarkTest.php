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

		Debug::$showLocation = FALSE;
		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');
		$connection->loadFile(APP_DIR . '/models/consumers.data.sql');
		Mapper::addConnection($connection);
		RecordHelper::cleanCache();

		// kalibrace
		ini_set('memory_limit', '10M'); // memory_get_peak_usage() ~ 8.5M
		dump('--- ActiveRecord ---');
		Consumer::objects()->first();
		Consumer::objects()->applyLimit(5)->load();
		

		timer('1');
		memory('1');
		$consumer = Consumer::objects()->first();
		$m = memory('1');
		$t = timer('1');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('10');
		memory('10');
		$consumers = Consumer::objects()->applyLimit(10);
		$consumers->load();
		$m = memory('10');
		$t = timer('10');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('100');
		memory('100');
		$consumers = Consumer::objects()->applyLimit(100);
		$consumers->load();
		$m = memory('100');
		$t = timer('100');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		ini_set('memory_limit', '12M');
		timer('1000');
		memory('1000');
		$consumers = Consumer::objects()->applyLimit(1000);
		$consumers->load();
		$m = memory('1000');
		$t = timer('1000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		ini_set('memory_limit', '128M');
		timer('50000');
		memory('50000');
		$consumers = Consumer::objects()->applyLimit(50000);
		$consumers->load();
		$m = memory('50000');
		$t = timer('50000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		// clean up
		Mapper::disconnect();
		RecordHelper::cleanCache();
	}

	public function testBenchmarkDibi() {

		Debug::$showLocation = FALSE;
		dibi::connect($this->config);
		dibi::loadFile(APP_DIR . '/models/consumers.structure.sql');
		dibi::loadFile(APP_DIR . '/models/consumers.data.sql');

		// kalibrace
		dump('--- Dibi ---');
		$row = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->fetch();


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

		timer('50000');
		memory('50000');
		$rows = dibi::dataSource('Consumers')->applyLimit(50000)->getResult()->detectTypes()->fetchAll();
		$m = memory('50000');
		$t = timer('50000');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		dibi::disconnect();
	}

	public function testGettingOneRecord() {

		Debug::$showLocation = FALSE;
		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/consumers.structure.sql');
		$connection->loadFile(APP_DIR . '/models/consumers.data.sql');
		Mapper::addConnection($connection);
		RecordHelper::cleanCache();

		// kalibrace
		dump('--- ActiveRecord: getting one record ---');
		Consumer::find(1);
		Consumer::findOne();
		Consumer::objects()->first();
		Consumer::objects()->applyLimit(1)->first();
		

		timer('1.1');
		memory('1.1');
		$consumer = Consumer::find(1);
		$m = memory('1.1');
		$t = timer('1.1');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('1.2');
		memory('1.2');
		$consumer = Consumer::findOne();
		$m = memory('1.2');
		$t = timer('1.2');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('1.3');
		memory('1.3');
		$consumer = Consumer::objects()->first();
		$m = memory('1.3');
		$t = timer('1.3');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		timer('1.4');
		memory('1.4');
		$consumer = Consumer::objects()->applyLimit(1)->first();
		$m = memory('1.4');
		$t = timer('1.4');
		dump(self::formatMemory($m));
		dump(self::formatTime($t));

		// clean up
		Mapper::disconnect();
		RecordHelper::cleanCache();
	}

	public function testGettingOneRow() {

		Debug::$showLocation = FALSE;
		dibi::connect($this->config);
		dibi::loadFile(APP_DIR . '/models/consumers.structure.sql');
		dibi::loadFile(APP_DIR . '/models/consumers.data.sql');

		// kalibrace
		dump('--- Dibi: getting one row ---');
		dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->fetch();


		timer('1');
		memory('1');
		$row = dibi::dataSource('Consumers')->applyLimit(1)->getResult()->detectTypes()->fetch();
		$m = memory('1');
		$t = timer('1');
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