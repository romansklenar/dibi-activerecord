<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class Events extends Object
{
	/** @var array of function(Record $sender);  Occurs before mapper deletes record */
	public $onDelete = array();

	/** @var array of function(Record $sender);  Occurs before mapper inserts record */
	public $onInsert = array();

	/** @var array of function(Record $sender);  Occurs before mapper updates record */
	public $onUpdate = array();

	// TODO nebo jen:

	/** @var array of function(Record $sender);  Occurs before mapper destroy record */
	public $onDestroy = array();

	/** @var array of function(Record $sender);  Occurs before mapper saves record */
	public $onSave = array();
}