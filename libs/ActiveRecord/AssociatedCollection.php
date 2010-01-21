<?php


/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://wiki.github.com/romansklenar/dibi-activerecord
 */
class AssociatedCollection extends ActiveCollection {

	/** @var ActiveRecord  related object */
	private $belongsTo;


	/**
	 * Object constructor.
	 *
	 * @param DibiDataSource $source
	 * @param string         $class
	 * @param ActiveRecord   $belongsTo
	 */
	public function __construct(DibiDataSource $source, $class, $belongsTo) {
		$this->belongsTo = $belongsTo;
		parent::__construct($source, $class);
	}


	/**
	 * Replaces (or appends) the item (ArrayAccess implementation).
	 * @param  int index
	 * @param  object
	 * @return void
	 * @throws InvalidArgumentException, NotSupportedException, ArgumentOutOfRangeException
	 */
	public function offsetSet($index, $item) {
		$class = $this->getItemType();
		$item = $this->belongsTo->getAssociation($class)->saveReferenced($this->belongsTo, $item);
		parent::offsetSet($index, $item);
	}


	/**
	 * Appends the specified element to the end of this collection.
	 * @param  mixed
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function append($item) {
		$class = $this->getItemType();
		$item = $this->belongsTo->getAssociation($class)->saveReferenced($this->belongsTo, $item);
		parent::append($item);
	}
}
