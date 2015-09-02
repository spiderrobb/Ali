<?php
namespace Ali;
class Input {
	public static function getInstance() {
		return new Input(array(
			'get'     => $_GET,
			'post'    => $_POST,
			'request' => $_REQUEST,
			'files'   => $_FILES,
			'url'     => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false
		));
	}
	
	// standard inputs
	private $_get;
	private $_post;
	private $_request;
	private $_files;
	private $_url;
	
	// controller vars
	private $_controller;
	private $_method;
	private $_args;
	
	// constructor
	public function __construct(array $args) {
		// init variables
		$this->_get        = array();
		$this->_post       = array();
		$this->_request    = array();
		$this->_files      = array();
		$this->_controller = Config::get('environment.default_controller');;
		$this->_method     = Config::get('environment.default_method');
		$this->_args       = array();
		$this->_url        = false;
		
		// looping through args
		foreach ($args as $key => $value) {
			$key = '_'.$key;
			if (isset($this->$key)) {
				$this->$key = $value;
			}
		}
		
		// checking for url
		if ($this->_url !== false) {
			$this->_parseURL($this->_url);
		}
	}

	// private function for parsing url
	private function _parseURL($url) {
		$url        = explode('?', $url);
		$url        = $url[0];
		$url        = substr($url, strlen(Config::get('environment.url_path')));
		$url        = str_replace('/', '\\', $url);
		$paths      = Config::get('environment.include_path');
		$controller = 'Controller\\';
		$method     = '';
		$arg        = '';
		$args       = array();
		$len        = strlen($url);
		$mode       = 'controller';
		for ($i = 0; $i<$len; $i++) {
			// finding end of controller
			if ($mode == 'controller' && $url{$i} === '-') {
				$mode = 'method';
				continue;
			}
			// finding end of method
			if ($mode == 'method' && $url{$i} === '\\') {
				$mode = 'arg';
				continue;
			}
			// finding end of arg
			if ($mode == 'arg' && $url{$i} === '\\') {
				$this->_args[] = $arg;
				$arg    = '';
				continue;
			}
			$$mode .= $url{$i};
		}
		if (!empty($arg)) {
			$this->_args[] = $arg;
		}
		if (!empty($method)) {
			$this->_method = $method;
		}
		while ($controller{strlen($controller)-1} === '\\') {
			$controller = substr($controller, 0, -1);
		}
		if ($controller !== 'Controller') {
			// looking through include paths for
			foreach ($paths as $prefix => $path) {
				if (class_exists($prefix.$controller)) {
					$controller = $prefix.$controller;
				}
			}
			// setting controller
			$this->_controller = $controller;
		}
	}

	// this function registers args
	public function registerArgs(array $args) {
		foreach ($args as $i => $key) {
			if (isset($this->_args[$i])) {
				$this->_get[$key] = $this->_args[$i];
			}
		}
	}

	// this function is for getting the  url
	public function getURL() {
		return $this->_url;
	}

	// function for getting controller
	public function getController() {
		return $this->_controller;
	}
	
	// function for getting controller
	public function getMethod() {
		return $this->_method;
	}
	
	// get data function
	public function get($key = null, $unset = null) {
		return $this->checkReturn($this->_get, $key, $unset);
	}
	
	// post function data
	public function post($key = null, $unset = null) {
		return $this->checkReturn($this->_post, $key, $unset);
	}
	
	// request data function
	public function request($key = null, $unset = null) {
		return $this->checkReturn($this->_request, $key, $unset);
	}

	// files data function
	public function files($key = null, $unset = null) {
		return $this->checkReturn($this->_files, $key, $unset);
	}

	// base get function
	public function checkReturn(array $array, $key = null, $unset = null) {
		if ($key === null) {
			return $array;
		} else if (!isset($array[$key])) {
			return $unset;
		}
		return $array[$key];
	}
	
}
