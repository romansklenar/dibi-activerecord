<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Collections
 */

/*namespace Nette\Collections;*/



/**
 * Represents a generic collection of key/value pairs.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Collections
 */
interface IMap extends ICollection, /*\*/ArrayAccess
{
	function add($key, $value);
	function search($item);
	function getKeys();
	//function ArrayAccess::offsetSet($offset, $value);
	//function ArrayAccess::offsetGet($offset);
	//function ArrayAccess::offsetUnset($offset);
	//function ArrayAccess::offsetExists($offset);
}
