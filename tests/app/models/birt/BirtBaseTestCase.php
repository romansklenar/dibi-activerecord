<?php

require_once 'PHPUnit/Framework.php';

/**
 * Common test class for classes in example.
 */
abstract class BirtBaseTestCase extends PHPUnit_Framework_TestCase {

	/** @var array  database configuration settings */
	protected $config = array(
		'driver' => 'sqlite3',
		'database' => ':memory:',
	);


	public function setUp() {
		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/birt.structure.sql');
		$connection->loadFile(APP_DIR . '/models/birt.data.sql');
		Mapper::addConnection($connection);
		CacheHelper::cleanCache();
	}

	public function tearDown() {
		Mapper::disconnect();
		CacheHelper::cleanCache();
	}
}