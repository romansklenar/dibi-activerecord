<?php


/**
 * Company active record model
 *
 * @property int $id
 * @property Firm|NULL $clientOf
 * @property string $name
 * @property bool $isFirm
 *
 * @hasMany(People)
 */
abstract class Company extends ActiveRecord {

	/** @var string  table name */
	protected static $table = 'Companies';

}
