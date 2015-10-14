<?php
namespace Ali;

use Ali\Component\Html;

/**
 * Package
 * The package class is responsible for handling
 * all the css and javascript needed for any class.
 * This class is static so that any class or function
 * can call the methods of the Package Class
 */
class Package {
	// private static variable for singleton
	private static $_instance;

	/**
	 * this function gets an instance of the request package
	 *
	 * @return Package
	 */
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new Package();
		}
		return self::$_instance;
	}

	// declaring variables
	private $_meta;
	private $_js_packages;
	private $_js_files;
	private $_js_raw;
	private $_css_packages;
	private $_css_files;
	private $_css_raw;
	private $_paths;

	/**
	 * the constructor builds and initializes the resource and data arrays for Package
	 */
	public function __construct() {
		// checking if instance already exists
		if (isset(self::$_istance)) {
			throw new Exception('This class singleton already exists.');
		}

		// init variables
		$this->_meta         = array();
		$this->_js_packages  = array();
		$this->_js_files     = array();
		$this->_css_packages = array();
		$this->_js_raw       = array();
		$this->_css_files    = array();
		$this->_css_raw      = array();
		$this->_paths        = Config::get('environment.include_path');
		$this->_path         = Config::get('environment.url_path');
	}

	/**
	 * this function takes peta parameters to be included with the request
	 *
	 * @param array $params array of html [attribute]=>[value] pairs
	 *
	 * @return void
	 */
	public function addMeta($params) {
		$this->_meta[] = $params;
	}

	/**
	 * this function generates all the meta content
	 *
	 * @return void
	 */
	public function generateMeta() {
		foreach ($this->_meta as $tag) {
			echo Html::startTag('meta', $tag, true);
		}
	}
	/**
	 * this function takes a package name and includes any script or style sheets
	 * that match the given package for the current request
	 *
	 * @param string $package name of package
	 *
	 * @return void
	 */
	public function get($package) {
		// check for native view files
		$this->getScript($package);
		$this->getStyle($package);
	}

	/**
	 * this function takes a string of javascript and makes sure it it is executed 
	 * for the current request
	 *
	 * @param string $script string of javascript
	 *
	 * @return void
	 */
	public function addScript($script) {
		$this->_js_raw[] = $script;
	}
	/**
	 * this function includes any javascript files included in a package
	 *
	 * @param string $package name of package
	 *
	 * @return void
	 */
	public function getScript($package) {
		// checking if we have already included this package
		if (!in_array($package, $this->_js_packages)) {
			// check for package requirements
			$packages = Config::get('package');
			if (isset($packages[$package]['requires'])) {
				foreach ($packages[$package]['requires'] as $required_package) {
					$this->getScript($required_package);
				}
			}

			// linking scripts in packages configuration
			if (isset($packages[$package]['js'])) {
				foreach ($packages[$package]['js'] as $script) {
					if (substr($script, 0, 4) !== 'http') {
						$script = $this->_path.$script;
					}
					$this->linkScript($script);
				}
			}

			// building file location from package name
			$path = $package.'.js';
			if (file_exists($path)) {
				// linking script
				$this->linkScript($this->_path.$path);
			}

			// mark package as loaded
			$this->_js_packages[] = $package;
		}
	}
	// the linkScript function is used to link javascript files
	// to the current request
	public function linkScript($url) {
		$this->_js_files[] = $url;
	}
	// the generateScript function generates the html for
	// all the javascript files that have been linked to this request
	public function generateScript() {
		foreach ($this->_js_files as $link) {
			echo Html::tag('script', '', array('type'=>'text/javascript', 'charset'=>'utf-8', 'src'=>$link));
		}
	}
	// the generateCustomScript function generates the html for all
	// the javascript code added through the addScript function for this request
	public function generateCustomScript(){
		if (!empty($this->_js_raw)) {
			echo Html::tag('script', $this->getScriptCalls(), array(), false);
		}
	}
	// the getScriptLinks function returns the full list of javascript
	// urls that have been linked to this request
	public function getScriptLinks() {
		return $this->_js_files;
	}
	// the getScriptCalls function returns all the javascript code that
	// was added through the addScript function for this request
	public function getScriptCalls() {
		return implode(' ', $this->_js_raw);
	}
	// the addStyle function is for adding css to a page without
	// linking a css file
	public function addStyle($style) {
		$this->_css_raw[] = $style;
	}
	// the getStyle function is used to require the css
	// associated with the given package or class
	public function getStyle($package) {
		// checking if we have already included this package
		if (!in_array($package, $this->_css_packages)) {
			// check for package requirements
			$packages = Config::get('package');
			if (isset($packages[$package]['requires'])) {
				foreach ($packages[$package]['requires'] as $required_package) {
					$this->getStyle($required_package);
				}
			}

			// check for package configuration
			if (isset($packages[$package]['css'])) {
				foreach ($packages[$package]['css'] as $script) {
					if (substr($script, 0, 4) !== 'http') {
						$script = $this->_path.$script;
					}
					$this->linkStyle($script);
				}
			}
			
			// building file location from package name
			$path = $package.'.css';
			if (file_exists($path)) {
				// linking script
				$this->linkStyle($this->_path.$path);
			}

			// mark package as loaded
			$this->_css_packages[] = $package;
		}
	}
	// the linkStyle function is used to link css files
	// to the current request
	public function linkStyle($url) {
		$this->_css_files[] = $url;
	}
	// the generateStyle function generates the html for
	// all the css files that have been linked to this request
	public function generateStyle() {
		foreach ($this->_css_files as $link) {
			echo Html::startTag('link', array('rel'=>'stylesheet', 'href'=>$link));
		}
	}
	// the generateCustomStyle function generates the html for all
	// the css styles added through the addStyle function for this request
	public function generateCustomStyle() {
		if (!empty($this->_css_raw)) {
			echo Html::tag('style', $this->getStyleLinks(), array(), false);
		}
	}
	// the getStyleLinks function returns the full list of css urls
	// that have been linked to this request
	public function getStyleLinks() {
		return implode(' ', $this->_css_raw);
	}

	public function getAssetPath($path) {
		return $this->_path.$path;
	}
}
