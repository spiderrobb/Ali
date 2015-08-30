<?php
namespace Ali\Base;
interface UserInterface {
	public static function getInstance();
	public function __construct();
	//public function login($email, $password);
	//public function logout();
	public function checkPermissions($permissions);
	//public function getXSRFToken($key);
}
?>