<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for access to static methods and variables.
 * This test shows what is fucked up on implementation of 
 * static methods and variables when you want 
 * run code under both PHP 5.2 and PHP 5.3
 */
class LateStaticBindingsTest extends PHPUnit_Framework_TestCase {

	/** @var M */
	public $model;


	function setUp() {
		$this->model = new M;
	}


	/***************** class *********************/


	function testInstanceGetClass() {
		$this->assertEquals('M', $this->model->getClass());
		$this->markTestSkipped('Must works everyway!');
	}

	function testInstanceGetClassByReflection() {
		$this->assertEquals('M', $this->model->getReflection()->name);
		$this->markTestSkipped('Must works everyway!');
	}



	function testStaticGetClassByReflection() {
		$this->assertEquals('M', M::getReflection()->name); # Failed: LateStaticBindingsTest
	}

	function testStaticGetClass() {
		$this->assertEquals('M', M::getClass()); # Failed: LateStaticBindingsTest
	}

	function testStaticClassByGetClass() {
		$this->assertEquals('M', M::classByGetClass()); # Failed: AR
	}

	function testStaticClassByGetCalledClass() {
		$this->assertEquals('M', M::classByGetCalledClass());
	}


	/***************** table *********************/


	function testInstanceGetTableBySelf() {
		$this->assertEquals('t', $this->model->getTableBySelf()); # Failed: DEFAULT_TABLE
	}

	function testInstanceGetTableByStatic() {
		$this->assertEquals('t', $this->model->getTableByStatic());
	}

	function testStaticTableBySelf() {
		$this->assertEquals('t', M::tableBySelf()); # Failed: DEFAULT_TABLE
	}

	function testStaticTableByStatic() {
		$this->assertEquals('t', M::tableByStatic());
	}


	/***************** primary *********************/


	function testInstanceGetPrimaryBySelf() {
		$this->assertEquals('p', $this->model->getPrimaryBySelf()); # Fails: DEFAULT_PRIMARY
	}

	function testInstanceGetPrimaryByStatic() {
		$this->assertEquals('p', $this->model->getPrimaryByStatic());
	}

	function testStaticPrimaryBySelf() {
		$this->assertEquals('p', M::primaryBySelf()); # Fails: DEFAULT_PRIMARY
	}

	function testStaticPrimaryByStatic() {
		$this->assertEquals('p', M::primaryByStatic());
	}


	/***************** connection *********************/


	function testInstanceGetConnectionByConstant() {
		$this->assertEquals('c', $this->model->getConnectionByConstant());
	}

	function testStaticConnectionByConstant() {
		$this->assertEquals('c', M::connectionByConstant());
	}

	function testStaticConnectionByConstantByParent() {
		$this->assertEquals('c', M::connectionByConstantByParent()); # Undefined class constant 'CONNECTION'
	}

	function testStaticConnectionByConstantBySelf() {
		$this->assertEquals('c', M::connectionByConstantBySelf()); # Undefined class constant 'CONNECTION'
	}

	function testStaticConnectionByConstantByStatic() {
		$this->assertEquals('c', M::connectionByConstantByStatic());
	}


	/***************** create *********************/


	function testStaticCreateByGetClass() {
		$model = M::createByGetClass();
		$this->assertType('M', $model); # Fails: AR
	}

	function testStaticCreateByGetCalledClass() {
		$model = M::createByGetCalledClass();
		$this->assertType('M', $model);
	}

	function testStaticCreateBySelf() {
		$model = M::createBySelf();
		$this->assertType('M', $model); # Fails: AR
	}

	function testStaticCreateByStatic() {
		$model = M::createByStatic();
		$this->assertType('M', $model);
	}
}


class AR extends Object {

	protected static $registry;
	protected static $table;

	const DEFAULT_TABLE = 'DEFAULT_TABLE';
	const DEFAULT_PRIMARY = 'DEFAULT_PRIMARY';
	const FATAL = 'Fatal error:  Access to undeclared static property';
	const UNDEFINED = "Undefined class constant 'CONNECTION'";


	/***************** class *********************/


	static function classByGetClass() {
		return get_class();
	}

	static function classByGetCalledClass() {
		return get_called_class();
	}


	/***************** table *********************/


	function getTableBySelf() {
		return (isset(self::$table)) ? self::$table : self::DEFAULT_TABLE;
	}

	function getTableByStatic() {
		return (isset(static::$table)) ? static::$table : self::DEFAULT_TABLE;
	}

	static function tableBySelf() {
		return (isset(self::$table)) ? self::$table : self::DEFAULT_TABLE;
	}

	static function tableByStatic() {
		return (isset(static::$table)) ? static::$table : self::DEFAULT_TABLE;
	}


	/***************** primary *********************/


	function getPrimaryBySelf() {
		return (isset(self::$primary)) ? self::$primary : self::DEFAULT_PRIMARY;
	}

	function getPrimaryByStatic() {
		return (isset(static::$primary)) ? static::$primary : self::DEFAULT_PRIMARY;
	}

	static function primaryBySelf() {
		return (isset(self::$primary)) ? self::$primary : self::DEFAULT_PRIMARY;
	}

	static function primaryByStatic() {
		return (isset(static::$primary)) ? static::$primary : self::DEFAULT_PRIMARY;
	}


	/***************** connection *********************/


	public function getConnectionByConstant() {
		$class = get_class($this);
		return $class::CONNECTION;
	}

	static function connectionByConstant() {
		$class = get_called_class();
		return $class::CONNECTION;
	}

	static function connectionByConstantByParent() {
		return self::UNDEFINED; // parent::CONNECTION
	}

	static function connectionByConstantBySelf() {
		return self::UNDEFINED; // self::CONNECTION;
	}

	static function connectionByConstantByStatic() {
		return static::CONNECTION;
	}


	/***************** create *********************/


	static function createByGetClass() {
		$class = get_class();
		return new $class();
	}


	static function createByGetCalledClass() {
		$class = get_called_class();
		return new $class();
	}

	static function createBySelf() {
		return new self();
	}

	static function createByStatic() {
		return new static();
	}
}

class M extends AR {
	protected static $table = 't';
	protected static $primary = 'p';
	const CONNECTION = 'c'; // TABLE, PRIMARY, FOREIGN, MAPPER_CLASS, CONNECTION -- ma ale jednu chybu: jako PK nemuze byt pole
}