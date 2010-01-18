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
		'formatDateTime' => "'Y-m-d H:i:s'",
	);


	public function setUp() {
		$connection = ActiveMapper::connect($this->config);
		$connection->loadFile(APP_DIR . '/models/example/db.structure.sql');
		$connection->loadFile(APP_DIR . '/models/example/db.data.sql');

		RecordHelper::cleanCache();
	}

	public function tearDown() {
		ActiveMapper::disconnect();
		RecordHelper::cleanCache();
	}
}