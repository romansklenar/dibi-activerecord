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
Debug::$showLocation = TRUE;


// 2b) load configuration from config.ini file
$config = Environment::loadConfig();

// 2c) check if needed directories are writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('tempDir')) . "' writable!");
}

if (!is_writable(Environment::getVariable('logDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('logDir')) . "' writable!");
}

$config = array(
	'driver' => 'sqlite3',
	'database' => ':memory:',
);
$connection = ActiveMapper::connect($config);
$connection->loadFile(APP_DIR . '/models/birt/birt.structure.sql');
$connection->loadFile(APP_DIR . '/models/birt/birt.data.sql');

$connection->loadFile(APP_DIR . '/models/example/db.structure.sql');
$connection->loadFile(APP_DIR . '/models/example/db.data.sql');

$connection = ActiveMapper::connect($config, '#rails_style');
$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_rails.structure.sql');
$connection->loadFile(APP_DIR . '/models/one-to-one/o2o_rails.data.sql');
$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_rails.structure.sql');
$connection->loadFile(APP_DIR . '/models/many-to-one/m2o_rails.data.sql');
$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_rails.structure.sql');
$connection->loadFile(APP_DIR . '/models/many-to-many/m2m_rails.data.sql');


$people = Person::objects();
Debug::dump($people->getArrayCopy());
Debug::dump(strip(dibi::$sql));

Debug::dump($people->filter('[companyId] = 1')->getArrayCopy());
Debug::dump(strip(dibi::$sql));



Inflector::$railsStyle = TRUE;
$car = Car::find(1);
Debug::dump($car);
Debug::dump(strip(dibi::$sql));

Debug::dump($car->guest); // guest_id = 2
Debug::dump(strip(dibi::$sql));

$car->guest = Guest::find(1);
Debug::dump($car->guest);
Debug::dump(strip(dibi::$sql));

Debug::dump($car->guest->car);
Debug::dump(strip(dibi::$sql));



$album = Album::find(1);
$album->name = 'Best of';
$album->save();
Debug::dump($album);
Debug::dump(strip(dibi::$sql));

Debug::dump($album->songs->name);
Debug::dump($album->songs->getPairs('id', 'name')); // something like fetchPairs

$ids = $album->songs->id;
$album->destroy();
Debug::dump(Album::count(1) == 0);

ActiveMapper::disconnect();