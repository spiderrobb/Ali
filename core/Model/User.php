<?php
namespace Ali\Model;

use Ali\Config;
use Ali\DB\Connection;
use Ali\DB\ActiveRecord;

class User extends ActiveRecord {
	public $confirm_password;
	public function __construct() {
		// setting up active record
		parent::__construct(Connection::getInstance(), 'block_party.user');
		$this->confirm_password = null;
	}
	protected function _afterLoad() {
		$this->confirm_password = $this->password;
	}
	/*
	public function getUserType() {
		$user_type = new UserType();
		return $user_type->findByPK($this->user_type_id);
	}
	*/
	protected function _passwordHash($password) {
		return sha1($password);
	}
	/*
	public function getPermissionsType() {
		return 'user';
	}
	public function getSessionType() {
		if ($this->id === null) {
			return self::NON_AUTHENTICATED;
		} else if (in_array($this->email, Config::get('user.admins', array()))
			|| strtolower($this->email) === strtolower(Config::get('user.admin.email', ''))
		) {
			return self::SYSTEM_ADMIN;
		}
		return self::AUTHENTICATED;
	}
	*/
	public function getPermissions($permissions = array()) {

		/*
		static $perms = false;
		if ($perms === false) {
			// getting inherited permissions
			$userType = $this->getUserType();
			if ($userType !== false) {
				$permissions = $userType->getPermissions($permissions);
			}
			$perms = parent::getPermissions($permissions);
		}
		return $perms;
		*/
	}
	public function checkPermissions($permissions = array()) {
		return true;
	}
	protected function _beforeSave() {
		// converting extends from string to type
		/*
		if ($this->user_type_id == 0) {
			$this->user_type_id = null;
		}

		// setting up array for any errors
		$errors = array();
		
		// finding type
		$check_user = self::getInstance()->findByPK(array(
			'email' => $this->email
		));
		// checking if this is a new type
		if ($this->_is_new) {
			// making sure type does not already exist
			if ($check_user !== false) {
				$errors['email'][] = 'This email address is already registered.';
			}
		} else {
			if ($check_user !== false && $this->id !== $check_user->id) {
				$errors['email'][] = 'This email address is already registered.';
			}
		}

		// checking if user type id is null
		if ($this->user_type_id === null) {
			$errors['user_type_id'][] = 'Invalid User Type.';
		}

		// checking for password
		if ($this->password !== $this->confirm_password) {
			$errors['password'][] = 'Password and Confirm Password do not match.';
		} else if ($this->password !== $this->_original_data['password']) {
			$this->password = $this->_passwordHash($this->password);
		}

		// returning errors
		if (!empty($errors)) {
			return array(
				'errors' => $errors
			);
		}

		// all is good
		return parent::_beforeSave();
		*/
	}
}
?>