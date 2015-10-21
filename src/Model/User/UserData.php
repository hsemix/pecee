<?php 
namespace Pecee\Model\User;
use Pecee\DB\DBTable;
use Pecee\Model\Model;

class UserData extends Model {
	public function __construct($userId = null, $key = null, $value = null) {

        $table = new DBTable();
        $table->column('userId')->bigint()->index();
        $table->column('key')->string(255)->index();
        $table->column('value')->longtext()->nullable();

		parent::__construct($table);

        $this->userId = $userId;
        $this->key = $key;
        $this->value = $value;
	}
	public function save() {
		if(self::scalar('SELECT `key` FROM {table} WHERE `key` = %s AND `userId` = %s', $this->key, $this->userId)) {
			parent::update();
		} else {
			parent::save();
		}
	}

	public static function removeAll($userId) {
		self::NonQuery('DELETE FROM {table} WHERE `userId` = %s', array($userId));
	}
	public static function getByUserId($userId) {
		return self::FetchAll('SELECT * FROM {table} WHERE `userId` = %s', array($userId));
	}
}