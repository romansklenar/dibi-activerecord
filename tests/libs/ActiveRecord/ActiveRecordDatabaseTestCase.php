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
		$connection = Mapper::connect($this->config);
		$connection->loadFile(APP_DIR . '/models/birt.structure.sql');
		$connection->loadFile(APP_DIR . '/models/birt.data.sql');

		$connection = Mapper::connect($this->config, '#authors');
		$connection->loadFile(APP_DIR . '/models/authors.structure.sql');
		$connection->loadFile(APP_DIR . '/models/authors.data.sql');

		$connection = Mapper::connect($this->config, '#nette_style');
		$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_nette.structure.sql');
		$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_nette.data.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_nette.structure.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_nette.data.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_nette.structure.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_nette.data.sql');

		$connection = Mapper::connect($this->config, '#rails_style');
		$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_rails.structure.sql');
		$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_rails.data.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_rails.structure.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_rails.data.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_rails.structure.sql');
		$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_rails.data.sql');

		RecordHelper::cleanCache();
		Inflector::$railsStyle = FALSE;
		ActiveRecordCollection::$loadImmediately = FALSE;
	}

	public function tearDown() {
		Mapper::disconnect();
		Mapper::disconnect('#authors');
		Mapper::disconnect('#nette_style');
		Mapper::disconnect('#rails_style');
		RecordHelper::cleanCache();
	}

}