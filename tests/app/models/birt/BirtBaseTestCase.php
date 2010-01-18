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
		'formatDateTime' => "'Y-m-d H:i:s'",
	);


	public function setUp() {
		$connection = ActiveMapper::connect($this->config);
		$connection->loadFile(APP_DIR . '/models/birt/birt.structure.sql');
		$connection->loadFile(APP_DIR . '/models/birt/birt.data.sql');
		RecordHelper::cleanCache();
	}

	public function tearDown() {
		ActiveMapper::disconnect();
		RecordHelper::cleanCache();
	}
}