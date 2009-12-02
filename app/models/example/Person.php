<?php


/**
 * People active record model
 *
 * @property int $id
 * @property Firm|NULL $clientOf
 * @property string $name
 * @property string $type
 *
 * @belongsTo(Company)
 */
class Person extends ActiveRecord {

}
