<?php

/**
 * Dibi ActiveRecord test bootstrap file.
 *
 * @copyright  Copyright (c) 2009 Roman Sklenar
 * @package    ActiveRecord
 */

// absolute filesystem path to the libraries
define('LIBS_DIR', realpath(dirname(__FILE__) . '/../libs'));

// absolute filesystem path to the application root
define('APP_DIR', realpath(dirname(__FILE__) . '/../app'));

// load Nette Framework
require_once LIBS_DIR . '/Nette/loader.php';
require_once LIBS_DIR . '/dibi/dibi.php';

// load configuration from config.ini file
Environment::loadConfig(dirname(__FILE__) . '/config.ini');

// check if needed directories are writable
if (!is_writable(Environment::getVariable('tempDir')))
	die("Make directory '" . realpath(Environment::getVariable('tempDir')) . "' writable!");

if (!is_writable(Environment::getVariable('logDir')))
	die("Make directory '" . realpath(Environment::getVariable('logDir')) . "' writable!");

// setup Nette\Debug
Debug::disableProfiler();
Debug::$showLocation = TRUE;
Debug::$strictMode = TRUE;
