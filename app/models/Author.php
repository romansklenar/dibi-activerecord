<?php


/**
 * Author active record model
 *
 * @belongsTo(Portfolio, House)
 * @hasOne(ProjectManager, bossId => Author)
 * @hasMany(Milestones)
 * @hasAndBelongsToMany(Categories)
 *
 *
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $firstname
 * @property string $lastname
 * @property int $credit
 */
class Author extends ActiveRecord {

	protected static $connection = '#authors';


	/**
	 * @retrun array
	 */
	public function getDefaultValues() {
		return parent::getDefaultValues();
	}


	/**
	 * @retrun DataStorage
	 */
	public function getStorage() {
		return parent::getStorage();
	}
}

