<?php

/**
 * dibi - tiny'n'smart database abstraction layer
 * ----------------------------------------------
 *
 * Copyright (c) 2005, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "dibi license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://dibiphp.com
 *
 * @copyright  Copyright (c) 2005, 2009 David Grudl
 * @license    http://dibiphp.com/license  dibi license
 * @link       http://dibiphp.com
 * @package    dibi
 */


/**
 * The dibi driver for SQLite database.
 *
 * Connection options:
 *   - 'database' (or 'file') - the filename of the SQLite database
 *   - 'formatDate' - how to format date in SQL (@see date)
 *   - 'formatDateTime' - how to format datetime in SQL (@see date)
 *   - 'dbcharset' - database character encoding (will be converted to 'charset')
 *   - 'charset' - character encoding to set (default is UTF-8)
 *   - 'resource' - connection resource (optional)
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009
 * @package    dibi
 */
class DibiSqlite3Driver extends DibiObject implements IDibiDriver
{
	/** @var SQLite3  Connection resource */
	private $connection;

	/** @var SQLite3Result  Resultset resource */
	private $resultSet;

	/** @var string  Date and datetime format */
	private $fmtDate, $fmtDateTime;

	/** @var string  character encoding */
	private $dbcharset, $charset;



	/**
	 * @throws DibiException
	 */
	public function __construct()
	{
		if (!extension_loaded('sqlite3')) {
			throw new DibiDriverException("PHP extension 'sqlite3' is not loaded.");
		}
	}



	/**
	 * Connects to a database.
	 * @return void
	 * @throws DibiException
	 */
	public function connect(array &$config)
	{
		DibiConnection::alias($config, 'database', 'file');
		$this->fmtDate = isset($config['formatDate']) ? $config['formatDate'] : 'U';
		$this->fmtDateTime = isset($config['formatDateTime']) ? $config['formatDateTime'] : 'U';

		if (isset($config['resource']) && $config['resource'] instanceof SQLite3) {
			$this->connection = $config['resource'];

		} elseif ($config['database'] !== ':memory:' && !is_readable($config['database'])) {
			throw new FileNotFoundException('Unable to open database file ' . $config['database']);
				
		} else {
			try {
				$this->connection = new SQLite3($config['database']);
			} catch (Exception $e) {
				throw new DibiDriverException($e->getMessage());
			}
		}

		$this->dbcharset = empty($config['dbcharset']) ? 'UTF-8' : $config['dbcharset'];
		$this->charset = empty($config['charset']) ? 'UTF-8' : $config['charset'];
		if (strcasecmp($this->dbcharset, $this->charset) === 0) {
			$this->dbcharset = $this->charset = NULL;
		}

		// enable foreign keys support (defaultly disabled; if disabled then foreign key constraints are not enforced)
		// @see: http://www.sqlite.org/foreignkeys.html
		$version = SQLite3::version();
		if ($version['versionNumber'] >= '3006019')
			$this->query("PRAGMA foreign_keys = ON");
	}



	/**
	 * Disconnects from a database.
	 * @return void
	 */
	public function disconnect()
	{
		$this->connection->close();
	}



	/**
	 * Executes the SQL query.
	 * @param  string      SQL statement.
	 * @return IDibiDriver
	 * @throws DibiDriverException
	 */
	public function query($sql)
	{
		if ($this->dbcharset !== NULL) {
			$sql = iconv($this->charset, $this->dbcharset . '//IGNORE', $sql);
		}

		DibiDriverException::tryError();
		$this->resultSet = $this->connection->query($sql);

		if (DibiDriverException::catchError($msg)) {
			throw new DibiDriverException($msg, $this->connection->lastErrorCode(), $sql);
		}
		
		return clone $this;
	}



	/**
	 * Gets the number of affected rows by the last INSERT, UPDATE or DELETE query.
	 * @return int|FALSE  number of rows or FALSE on error
	 */
	public function getAffectedRows()
	{
		return $this->connection->changes();
	}



	/**
	 * Retrieves the ID generated for an AUTO_INCREMENT column by the previous INSERT query.
	 * @return int|FALSE  int on success or FALSE on failure
	 */
	public function getInsertId($sequence)
	{
		return $this->connection->lastInsertRowID();
	}



	/**
	 * Begins a transaction (if supported).
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	public function begin($savepoint = NULL)
	{
		$this->query($savepoint ? "SAVEPOINT $savepoint" : 'BEGIN');
	}



	/**
	 * Commits statements in a transaction.
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	public function commit($savepoint = NULL)
	{
		$this->query($savepoint ? "RELEASE SAVEPOINT $savepoint" : 'COMMIT');
	}



	/**
	 * Rollback changes in a transaction.
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	public function rollback($savepoint = NULL)
	{
		$this->query($savepoint ? "ROLLBACK TO SAVEPOINT $savepoint" : 'ROLLBACK');
	}



	/**
	 * Returns the connection resource.
	 * @return mixed
	 */
	public function getResource()
	{
		return $this->connection;
	}



	/********************* SQL ****************d*g**/



	/**
	 * Encodes data for use in a SQL statement.
	 * @param  mixed     value
	 * @param  string    type (dibi::TEXT, dibi::BOOL, ...)
	 * @return string    encoded value
	 * @throws InvalidArgumentException
	 */
	public function escape($value, $type)
	{
		switch ($type) {
		case dibi::TEXT:
		case dibi::BINARY:
			return "'" . sqlite_escape_string($value) . "'";

		case dibi::BINARY: // SQLite 3
			return "X'" . bin2hex((string) $value) . "'";

		case dibi::IDENTIFIER:
			return '[' . str_replace('.', '].[', strtr($value, '[]', '  ')) . ']';

		case dibi::BOOL:
			return $value ? 1 : 0;

		case dibi::DATE:
			return $value instanceof DateTime ? $value->format($this->fmtDate) : date($this->fmtDate, $value);

		case dibi::DATETIME:
			return $value instanceof DateTime ? $value->format($this->fmtDateTime) : date($this->fmtDateTime, $value);

		default:
			throw new InvalidArgumentException('Unsupported type.');
		}
	}



	/**
	 * Decodes data from result set.
	 * @param  string    value
	 * @param  string    type (dibi::BINARY)
	 * @return string    decoded value
	 * @throws InvalidArgumentException
	 */
	public function unescape($value, $type)
	{
		if ($type === dibi::BINARY) {
			return $value;
		}
		throw new InvalidArgumentException('Unsupported type.');
	}



	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 * @param  string &$sql  The SQL query that will be modified.
	 * @param  int $limit
	 * @param  int $offset
	 * @return void
	 */
	public function applyLimit(&$sql, $limit, $offset)
	{
		if ($limit < 0 && $offset < 1) return;
		$sql .= ' LIMIT ' . $limit . ($offset > 0 ? ' OFFSET ' . (int) $offset : '');
	}



	/********************* result set ****************d*g**/



	/**
	 * Returns the number of rows in a result set.
	 * @return int
	 * @throws NotSupportedException
	 */
	public function getRowCount()
	{
		throw new NotSupportedException('Row count is not available for unbuffered queries.');
	}



	/**
	 * Fetches the row at current position and moves the internal cursor to the next position.
	 * @param  bool     TRUE for associative array, FALSE for numeric
	 * @return array    array on success, nonarray if no next record
	 * @internal
	 */
	public function fetch($assoc)
	{
		$row = $this->resultSet->fetchArray($assoc ? SQLITE3_ASSOC : SQLITE3_NUM);
		
		$charset = $this->charset === NULL ? NULL : $this->charset . '//TRANSLIT';
		if ($row && ($assoc || $charset)) {
			$tmp = array();
			foreach ($row as $k => $v) {
				if ($charset !== NULL && is_string($v)) {
					$v = iconv($this->dbcharset, $charset, $v);
				}
				$tmp[str_replace(array('[', ']'), '', $k)] = $v;
			}
			return $tmp;
		}
		return $row;
	}



	/**
	 * Moves cursor position without fetching row.
	 * @param  int      the 0-based cursor pos to seek to
	 * @return boolean  TRUE on success, FALSE if unable to seek to specified record
	 * @throws NotSupportedException
	 */
	public function seek($row)
	{
		throw new NotSupportedException('Cannot seek an unbuffered result set.');
	}



	/**
	 * Frees the resources allocated for this result set.
	 * @return void
	 */
	public function free()
	{
		//$this->resultSet->finalize();
		$this->resultSet = NULL;
	}



	/**
	 * Returns metadata for all columns in a result set.
	 * @return array
	 */
	public function getColumnsMeta()
	{
		$count = $this->resultSet->numColumns();
		$res = array();
		for ($i = 0; $i < $count; $i++) {
			$name = str_replace(array('[', ']'), '', $this->resultSet->columnName($i));
			$pair = explode('.', $name);
			$res[$i] = array(
				'name'  => isset($pair[1]) ? $pair[1] : $pair[0],
				'table' => isset($pair[1]) ? $pair[0] : NULL,
				'fullname' => $name,
				'nativetype' => $this->resultSet->columnType($i),
			);

			switch ($res[$i]['nativetype']) {
				case SQLITE3_INTEGER:
					$res[$i]['nativetype'] = 'integer'; break;
				case SQLITE3_FLOAT:
					$res[$i]['nativetype'] = 'float'; break;
				case SQLITE3_TEXT:
					$res[$i]['nativetype'] = 'string'; break;
				case SQLITE3_BLOB:
					$res[$i]['nativetype'] = 'blob'; break;
				case SQLITE3_NULL:
					$res[$i]['nativetype'] = 'null'; break;
				default:
					throw new InvalidStateException("Unknown column type '" . $res[$i]['nativetype'] . "'");
			}
		}
		return $res;
	}



	/**
	 * Returns the result set resource.
	 * @return mixed
	 */
	public function getResultResource()
	{
		return $this->resultSet;
	}



	/********************* reflection ****************d*g**/



	/**
	 * Returns list of tables.
	 * @return array
	 */
	public function getTables()
	{
		$this->query("
			SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'
			UNION ALL
			SELECT name, type FROM sqlite_temp_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'"
		);
		$res = array();
		while ($row = $this->fetch(FALSE)) {
			$res[] = array(
				'name' => $row[0],
				'view' => $row[1] === 'view',
			);
		}
		$this->free();
		return $res;
	}



	/**
	 * Returns metadata for all columns in a table.
	 * @param  string
	 * @return array
	 */
	public function getColumns($table)
	{
		$this->query("
			SELECT sql FROM sqlite_master WHERE type = 'table' AND name = '$table'
			UNION ALL
			SELECT sql FROM sqlite_temp_master WHERE type = 'table' AND name = '$table'"
		);
		$meta = $this->fetch(TRUE);
		$this->free();

		$this->query("PRAGMA table_info([$table])");
		$res = array();
		while ($row = $this->fetch(TRUE)) {
			$column = $row['name'];
			$pattern = "/(\"$column\"|\[$column\]|$column)\s+[^,]+\s+PRIMARY\s+KEY\s+AUTOINCREMENT/Ui";
			$type = explode('(', $row['type']);

			$res[] = array(
				'name' => $column,
				'table' => $table,
				'fullname' => "$table.$column",
				'nativetype' => strtoupper($type[0]),
				'size' => isset($type[1]) ? (int) $type[1] : NULL,
				'nullable' => $row['notnull'] == '0',
				'default' => $row['dflt_value'],
				'autoincrement' => (bool) preg_match($pattern, $meta['sql']),
				'vendor' => $row,
			);
		}
		$this->free();
		return $res;
	}



	/**
	 * Returns metadata for all indexes in a table.
	 * @param  string
	 * @return array
	 */
	public function getIndexes($table)
	{
		$this->query("PRAGMA index_list([$table])");
		$res = array();
		while ($row = $this->fetch(TRUE)) {
			$res[$row['name']]['name'] = $row['name'];
			$res[$row['name']]['unique'] = (bool) $row['unique'];
		}
		$this->free();

		foreach ($res as $index => $values) {
			$this->query("PRAGMA index_info([$index])");
			while ($row = $this->fetch(TRUE)) {
				$res[$index]['columns'][$row['seqno']] = $row['name'];
			}
		}
		$this->free();

		$columns = $this->getColumns($table);
		foreach ($res as $index => $values) {
			$column = $res[$index]['columns'][0];
			$primary = FALSE;
			foreach ($columns as $info) {
				if ($column == $info['name']) {
					$primary = $info['vendor']['pk'];
					break;
				}
			}
			$res[$index]['primary'] = (bool) $primary;
		}

		return array_values($res);
	}



	/**
	 * Returns metadata for all foreign keys in a table.
	 * @param  string
	 * @return array
	 */
	public function getForeignKeys($table)
	{
		$this->query("PRAGMA foreign_key_list([$table])");
		$res = array();
		while ($row = $this->fetch(TRUE)) {
			$res[$row['id']]['name'] = $row['id']; // foreign key name
			$res[$row['id']]['local'][$row['seq']] = $row['from']; // local columns
			$res[$row['id']]['table'] = $row['table']; // referenced table
			$res[$row['id']]['foreign'][$row['seq']] = $row['to']; // referenced columns
			$res[$row['id']]['onDelete'] = $row['on_delete'];
			$res[$row['id']]['onUpdate'] = $row['on_update'];

			if ($res[$row['id']]['foreign'][0] == NULL) {
				$res[$row['id']]['foreign'] = NULL;
			}
		}
		$this->free();
		return array_values($res);
	}



	/********************* user defined functions ****************d*g**/



	/**
	 * Registers an user defined function for use in SQL statements.
	 * @param  string  function name
	 * @param  mixed   callback
	 * @param  int     num of arguments
	 * @return void
	 */
	public function registerFunction($name, $callback, $numArgs = -1)
	{
		$this->connection->createFunction($name, $callback, $numArgs);
	}



	/**
	 * Registers an aggregating user defined function for use in SQL statements.
	 * @param  string  function name
	 * @param  mixed   callback called for each row of the result set
	 * @param  mixed   callback called to aggregate the "stepped" data from each row
	 * @param  int     num of arguments
	 * @return void
	 */
	public function registerAggregateFunction($name, $rowCallback, $agrCallback, $numArgs = -1)
	{
		$this->connection->createAggregate($name, $rowCallback, $agrCallback, $numArgs);
	}

}
