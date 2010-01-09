<?php

require_once 'PHPUnit/Framework.php';

/**
 * Common test class for classes in example.
 */
abstract class ExampleBaseTestCase extends PHPUnit_Framework_TestCase {

	/** @var array  database configuration settings */
	protected $config = array(
		'driver' => 'sqlite3',
		'database' => ':memory:',
	);


	public function setUp() {
		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/example/db.structure.sql');
		$connection->loadFile(APP_DIR . '/models/example/db.data.sql');
		Mapper::addConnection($connection);

		CacheHelper::cleanCache();
	}

	public function tearDown() {
		Mapper::disconnect();
		CacheHelper::cleanCache();
	}
}