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
require LIBS_DIR . '/compatibility.php';


// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
$mode = (!Environment::isProduction() && !Environment::getHttpRequest()->isAjax()) ? Debug::DEVELOPMENT : Debug::PRODUCTION;
Debug::enable($mode);
Debug::enableProfiler();
Debug::$strictMode = TRUE;


// 2b) load configuration from config.ini file
$config = Environment::loadConfig();

// 2c) check if needed directories are writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('tempDir')) . "' writable!");
}

if (!is_writable(Environment::getVariable('logDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('logDir')) . "' writable!");
}

die('There is nothing to see.');