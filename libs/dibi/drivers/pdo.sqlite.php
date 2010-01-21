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


require_once(dirname(__FILE__) . '/pdo.php');

/**
 * The dibi driver for SQLite3 via PDO.
 *
 * Connection options:
 *   - 'dsn' - driver specific DSN
 *   - 'username' (or 'user')
 *   - 'password' (or 'pass')
 *   - 'options' - driver specific options array
 *   - 'resource' - PDO object (optional)
 *   - 'lazy' - if TRUE, connection will be established only when required
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009
 * @package    dibi
 */
class DibiPdoSqliteDriver extends DibiPdoDriver
{

	/**
	 * Connects to a database.
	 * @return void
	 * @throws DibiException
	 */
	public function connect(array &$config)
	{
		parent::connect($config);

		// enable foreign keys support (defaultly disabled; if disabled then foreign key constraints are not enforced)
		// @see: http://www.sqlite.org/foreignkeys.html
		$this->query("PRAGMA foreign_keys = ON");
	}

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

}
