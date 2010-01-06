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
	public static function getAssotiations(ObjectReflection $r, $table) {
		$cache = CacheHelper::getCache();
		$key = $table . '.assotiations';

		if (isset($cache[$key]))
			return $cache[$key];

		$ann = $r->getAnnotations();
		$arr = array(
			Association::BELONGS_TO => $ann[Association::BELONGS_TO],
			Association::HAS_ONE => $ann[Association::HAS_ONE],
			Association::HAS_MANY => $ann[Association::HAS_MANY],
			Association::HAS_AND_BELONGS_TO_MANY => $ann[Association::HAS_AND_BELONGS_TO_MANY],
		);

		$assc = array();
		foreach ($arr as $type => $annotations)
			foreach ($annotations as $annotation)
				foreach ($annotation->getValues() as $col => $tbl)
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
