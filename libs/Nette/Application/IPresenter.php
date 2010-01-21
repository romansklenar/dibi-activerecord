<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



/**
 * Defines method that must be implemented to allow a component to act like a presenter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IPresenter
{

	/**
	 * @param  PresenterRequest
	 * @return IPresenterResponse
	 */
	function run(PresenterRequest $request);

}
