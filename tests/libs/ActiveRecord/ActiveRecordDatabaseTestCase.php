<?php

require_once 'PHPUnit/Framework.php';

/**
 * Abstract class for testing ActiveRecord.
 */
abstract class ActiveRecordDatabaseTestCase extends PHPUnit_Framework_TestCase {

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

		$connection = new DibiConnection($this->config);
		$connection->loadFile(APP_DIR . '/models/authors.structure.sql');
		$connection->loadFile(APP_DIR . '/models/authors.data.sql');
		Mapper::addConnection($connection, '#authors');

		CacheHelper::cleanCache();
	}

	public function tearDown() {
		Mapper::disconnect();
		Mapper::disconnect('#authors');
		CacheHelper::cleanCache();
	}

}