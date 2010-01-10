<?php

/**
 * Dibi ActiveRecord demo application bootstrap file.
 *
 * @copyright  Copyright (c) 2009 Roman Sklenar
 * @package    ActiveRecord
 */


// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';
require LIBS_DIR . '/dibi/dibi.php';


// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
$mode = (!Environment::isProduction() && !Environment::getHttpRequest()->isAjax()) ? Debug::DEVELOPMENT : Debug::PRODUCTION;
Debug::enable($mode);
Debug::enableProfiler();
Debug::$strictMode = TRUE;
Debug::$maxDepth = 4;


// 2b) load configuration from config.ini file
$config = Environment::loadConfig();

// 2c) check if needed directories are writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('tempDir')) . "' writable!");
}

if (!is_writable(Environment::getVariable('logDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('logDir')) . "' writable!");
}


$connection = Mapper::connect(array(
	'driver' => 'sqlite3',
	'database' => ':memory:',
));
$connection->loadFile(APP_DIR . '/models/example/db.structure.sql');
$connection->loadFile(APP_DIR . '/models/example/db.data.sql');

Debug::dump($connection->fetchAll('SELECT * FROM [People] WHERE [companyId] = 1'));
Debug::dump(Person::objects()->filter('[companyId] = 1')->toArray());
Debug::dump(strip(dibi::$sql));

Mapper::disconnect();