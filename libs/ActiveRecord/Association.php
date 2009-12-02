<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class Association extends Object {

	/** @var DibiTableInfo */
	public $localTable;

	/** @var DibiTableInfo */
	public $foreignTable;

	/** @var array */
	private $meta = array();

	/** @var int */
	private $type;

	/** @var bool  use annotations inheritance? (experimental) */
	public static $inheritance = FALSE;

	/**#@+ association type */
	const BELONGS_TO = 'belongsTo'; // N:1
	const HAS_ONE = 'hasOne'; // 1:1
	const HAS_MANY = 'hasMany'; // 1:N
	const HAS_AND_BELONGS_TO_MANY = 'hasAndBelongsToMany'; // M:N
	/**#@-*/


	/**
	 * Association constructor.
	 * 
	 * @param string $type  association type constant
	 * @param string $localTable  local table name
	 * @param string $foreignTable  referenced table name
	 * @param string $localColumn  local column name
	 */
	public function __construct($type, $localTable, $foreignTable, $localColumn = NULL) {
		if (in_array($type, array(self::BELONGS_TO, self::HAS_ONE, self::HAS_MANY, self::HAS_AND_BELONGS_TO_MANY)))
			$this->type = $type;
		else
			throw new InvalidArgumentException("Unknown association type '$type' given.");
		
		$this->meta['local']['column'] = $localColumn;
		$this->meta['local']['table'] = Inflector::pluralize($localTable);
		$this->meta['foreign']['column'] = NULL;
		$this->meta['foreign']['table'] = Inflector::pluralize($foreignTable); //$this->type === self::BELONGS_TO || $this->type === self::HAS_ONE ? Inflector::pluralize($foreignTable) : $foreignTable;

		$this->init();
	}

	/**
	 * @return @void
	 */
	protected function init() {
		$this->getLocalTable();
		$this->getForeignTable();
		$this->detectLocalColumn();
		$this->detectForeignColumn();
	}


	/**
	 * Gets class assotiations.
	 * @param  string $r      class name
	 * @param  string $table  table name
	 * @return array of Association
	 */
	public static function getAssotiations(ReflectionClass $r, $table) {
		$cache = CacheHelper::getCache();
		$key = $table . '.assotiations';

		if (isset($cache[$key]))
			return $cache[$key];

		$arr = Association::parseAssotiations($r);
		$assc = array();

		foreach ($arr as $type => $tables)
			foreach ($tables as $col => $tbl)
				if (isset($assc[$tbl]))
					throw new InvalidStateException(
						"Ambiguous assotiations '$type' and '{$assc[$tbl]->type}' from $table to table $tbl found."
					);
				else
					$assc[$type][] = new Association($type, $table, $tbl, !is_numeric($col) ? $col : NULL);

		$cache->save($key, $assc, array(
			'files' => array($r->getFileName())
			// TODO: vsechny soubory predku
		));

		return $assc;
	}


	/**
	 * Gets class assotiations meta.
	 * @return array
	 */
	public static function parseAssotiations(Reflector $reflection) {
		if (self::$inheritance) {
			$tmp[Association::BELONGS_TO] = Annotations::getAll($reflection, Association::BELONGS_TO, TRUE);
			$tmp[Association::HAS_ONE] = Annotations::getAll($reflection, Association::HAS_ONE, TRUE);
			$tmp[Association::HAS_MANY] = Annotations::getAll($reflection, Association::HAS_MANY, TRUE);
			$tmp[Association::HAS_AND_BELONGS_TO_MANY] = Annotations::getAll($reflection, Association::HAS_AND_BELONGS_TO_MANY, TRUE);

		} else {
			$tmp[Association::BELONGS_TO] = Annotations::getAll($reflection, Association::BELONGS_TO);
			$tmp[Association::HAS_ONE] = Annotations::getAll($reflection, Association::HAS_ONE);
			$tmp[Association::HAS_MANY] = Annotations::getAll($reflection, Association::HAS_MANY);
			$tmp[Association::HAS_AND_BELONGS_TO_MANY] = Annotations::getAll($reflection, Association::HAS_AND_BELONGS_TO_MANY);

		}

		foreach ($tmp as $assotiation => & $values) {
			$res[$assotiation] = array();
			foreach ($values as & $tables) {
				$tables = $tables instanceof ArrayObject || is_array($tables) ? (array) $tables : array($tables);
				foreach ($tables as $key => & $table)
					if (String::startsWith($table, '> '))
						$table = ltrim($table, '> ');
				$res[$assotiation] = array_merge($res[$assotiation], $tables);
			}
		}
		return $res;
	}

	/**
	 * Detects local column name(s).
	 * @return string|array
	 */
	protected function detectLocalColumn() {
		if ($this->meta['local']['column'] === NULL) {
			if ($this->type === self::HAS_MANY || $this->type === self::HAS_AND_BELONGS_TO_MANY)
				foreach ($this->getLocalTable()->getPrimaryKey()->getColumns() as $column)
					$this->meta['local']['column'][] = $column->name;
			elseif ($this->type === self::BELONGS_TO || $this->type === self::HAS_ONE)
				$this->meta['local']['column'] = Inflector::singularize(lcfirst($this->meta['foreign']['table'])). 'Id';
		}
		return $this->meta['local']['column'];
	}

	/**
	 * Detects foreign column name(s).
	 * @return string|array
	 */
	protected function detectForeignColumn() {
		if ($this->meta['foreign']['column'] === NULL) {
			if ($this->type === self::HAS_MANY || $this->type === self::HAS_AND_BELONGS_TO_MANY)
				$this->meta['foreign']['column'] = Inflector::singularize(lcfirst($this->meta['local']['table'])) . 'Id';
			elseif ($this->type === self::BELONGS_TO || $this->type === self::HAS_ONE)
				foreach ($this->getForeignTable()->getPrimaryKey()->getColumns() as $column)
					$this->meta['foreign']['column'][] = $column->name;
		}
		return $this->meta['foreign']['column'];
	}


	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

	
	/**
	 * @return DibiTableInfo
	 */
	public function getLocalTable() {
		if (!$this->localTable instanceof DibiTableInfo) {
				$table = Inflector::singularize($this->meta['local']['table']);
				$record = new $table;
				$this->localTable = $record->getTableInfo();
		}
		return $this->localTable;
	}


	/**
	 * @return DibiTableInfo
	 */
	public function getForeignTable() {
		if (!$this->foreignTable instanceof DibiTableInfo) {
				$table = Inflector::singularize($this->meta['foreign']['table']);
				$record = new $table;
				$this->foreignTable = $record->getTableInfo();
		}
		return $this->foreignTable;
	}


	/**
	 * @return DibiColumnInfo|array of DibiColumnInfo
	 */
	public function getLocalColumn() {
		if (is_array($name = $this->meta['local']['column']))
			foreach ($this->meta['local']['column'] as $name)
				$col[] = $this->getLocalTable()->getColumn($name);
		else
			$col = $this->getLocalTable()->getColumn($name);

		return $col;
	}


	/**
	 * @return DibiColumnInfo|array of DibiColumnInfo
	 */
	public function getForeignColumn() {
		if (is_array($name = $this->meta['foreign']['column']))
			foreach ($this->meta['foreign']['column'] as $name)
				$col[] = $this->getForeignTable()->getColumn($name);
		else
			$col = $this->getForeignTable()->getColumn($name);

		return $col;
	}
}
