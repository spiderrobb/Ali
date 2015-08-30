<?php
namespace Ali;

use Ali\Model\User;
use Ali\Model\Session;
use Ali\Base\UserInterface;

class UserSession extends User implements UserInterface {
	// this function gets an instance of a user
	public static function getInstance() {
		static $user = false;
		// creating user session
		if (!isset($_SESSION['user'])) {
			// creating new user session
			/*
			$session = Session::getInstance();
			$session->ip_address = $_SERVER['REMOTE_ADDR'];
			$session->user_agent = $_SERVER['HTTP_USER_AGENT'];
			$session->save();
			*/

			// building session
			$_SESSION['user'] = array(
				'start'      => $_SERVER['REQUEST_TIME'],
				'type'       => 'public',
				//'session_id' => $session->id
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
	/*
	public function login($email, $password) {
		// checking if email is the system admin
		if ($email === Config::get('user.admin.email')) {
			if ($password !== Config::get('user.admin.password')) {
				return array('errors' => array(
					'password' => 'Incorrect Password, Please try again.'
				));
			}
			$this->setSessionType('system');
		} else {
			// finding user with email
			$check = $this->findByPK(array(
				'email' => $email
			));
			if ($check === false) {
				return array('errors' => array(
					'email' => 'This email is not linked to a registered account.'
				));
			}
			// validating password
			if ($this->password !== $this->_passwordHash($password)) {
				return array('errors' => array(
					'password' => 'Incorrect Password, Please try again.'
				));
			}
			// successful log in
			$_SESSION['user']['id']   = $this->id;
			if (in_array($email, Config::get('user.admins'))) {
				$this->setSessionType('system');
			}
			// saving user_id in session
			$session = Session::getInstance()->findByPK($_SESSION['user']['session_id']);
			if ($session !== false) {
				$session->user_id = $this->id;
				$session->save();
			}
		}
		return true;
	}
	public function logout() {
		$_SESSION = array();
		return true;
	}
	public function getXSRFToken($key) {
		return sha1($_SESSION['user']['start'].$key);
	}
	public function getSessionId() {
		if (isset($_SESSION['user']['session_id'])) {
			return $_SESSION['user']['session_id'];
		}
		return 0;
	}
	public function setSessionType($type) {
		$_SESSION['user']['type'] = $type;
	}
	public function getSessionType() {
		if (isset($_SESSION['user']['type']) && $_SESSION['user']['type'] == 'system') {
			return self::SYSTEM_ADMIN;
		}
		return parent::getSessionType();
	}
	*/
}
?>