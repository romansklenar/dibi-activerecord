<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Reflection
 */

/*namespace Nette\Reflection;*/



/**
 * Basic annotation implementation.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Reflection
 */
class Annotation extends /*Nette\*/Object implements IAnnotation
{

	public function __construct(array $values)
	{
		foreach ($values as $k => $v) {
			$this->$k = $v;
		}
	}


	/**
	 * Returns default annotation.
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
