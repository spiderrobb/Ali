<?php
namespace Ali;

use Ali\Model\User;
use Ali\Model\Session;
use Ali\Base\UserInterface;

class UserSession implements UserInterface {
	// this function gets an instance of a user
	public static function getInstance() {
		static $user = false;
		// creating user session
		if (!isset($_SESSION['user'])) {

			// building session
			$_SESSION['user'] = array(
				'start'      => $_SERVER['REQUEST_TIME'],
				'type'       => 'public'
			);
		}
		// creating user if one does not exist
		if ($user === false) {
			$user = new UserSession();
			if (isset($_SESSION['user']['id'])) {
				$user->findByPK($_SESSION['user']['id']);
			}
		}
		return $user;
	}
	public function checkPermissions($permissions) {
		return true;
	}
}
