<?php
namespace Ali\Base;

use Ali\Config;
use Ali\Input;

abstract class ControllerAbstract implements ControllerInterface {
	use ViewLoader;
	public static function getUrl($method = null, array $get = array(), array $args = array()) {
		// changing '\\' to '/' to look better in the url
		$url = str_replace('\\', '/', get_called_class());
		// removing the begining 'Controller_' so url's look better
		$url = preg_replace('/.*?\/Controller\//', '', $url);
		
		// appending method to end of url if method is specified
		if (isset($method)) {
			$url .= '-'.$method.'/';
		} else if (!empty($args)) {
			$url .= '-/';
		} else {
			$url .= '/';
		}
		
		// appending args to the url
		foreach ($args as $arg) {
			$url .= preg_replace('/[^a-z0-9]/i','',$arg).'/';
		}
		
		// appending args to the end of url if there are any, and returning
		if (!empty($get)) {
			return Config::get('environment.url_path').$url.'?'.http_build_query($get);
		}
		// returning url with no args
		return Config::get('environment.url_path').$url;
	}

	public static function getAbsoluteURL($method = null, array $get = array(), array $args = array()) {
		$url = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$url .= 's';
		}
		$url .= '://'.$_SERVER['HTTP_HOST'].self::getURL($method, $get, $args);
		return $url;
	}

	public static function getRoles($method = null) {
		return Config::get('environment.default_role');
	}
	final public static function getPermissions($method = null) {
		$class = get_called_class();
		$perms = $class::getRoles($method);
		if (!is_array($perms)) {
			$perms = array($perms);
		}
		foreach ($perms as &$perm) {
			$perm = 'role.'.$perm;
		}
		if ($method === null) {
			$method = Config::get('environment.default_method');
		}
		$perms[] = $class.'-'.$method;
		$perms[] = $class.'-*';
		return $perms;
	}
	protected $_user;
	protected $_input;
	public function __construct(UserInterface $user, Input $input) {
		$this->_user  = $user;
		$this->_input = $input;
		$this->init();
	}
	public function getTemplate($method = null) {
		$templates = $this->templates();
		$method    = strtolower($method);
		if (isset($templates[$method])) {
			return $templates[$method];
		}
		return Config::get('environment.default_template');
	}
	public function templates() {
		return array();
	}
	public function getTitle($method = null) {
		$titles = $this->titles();
		$method = strtolower($method);
		if (isset($titles[$method])) {
			return $titles[$method];
		}
		return get_called_class().'-'.$method;
	}
	public function titles() {
		return array();
	}
	public function init() {
	}
}
?>