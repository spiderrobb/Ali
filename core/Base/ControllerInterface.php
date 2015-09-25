<?php
namespace Ali\Base;
use Ali\Input;
interface ControllerInterface {
	public function __construct(UserInterface $user, Input $input);
	public function init();
	public function getTitle($method = null);
	public function getTemplate($method = null);
	public static function getRoles($method = null);
	public static function getPermissions($method = null);
	public static function getURL($method = null, array $get = array(), array $args = array());
	public static function getAbsoluteURL($method = null, array $get = array(), array $args = array());
}
?>