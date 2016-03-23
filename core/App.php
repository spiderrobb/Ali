<?php
namespace Ali;

use Exception;
use Ali\Base\UserInterface;

/**
 * The App Class handles validation and permissions checking for any controller
 * method combination, in addition to validing controller:method requests it prints
 * the correct html based on controller/template for html requests and
 * also prints a json alternative for ajax requests.
 */
class App {
	// class constants
	const PERM_GRANTED = 1;
	const PERM_DENIED  = 0;
	const PERM_404     = 404;
	
	// class variables
	protected $_input;
	protected $_controller;
	protected $_method;
	protected $_user;
	protected $_package;
	
	/**
	 * The constructor takes a controller class name and method
	 * and performs the prepares the App to be rendered via the html or json functions
	 *
	 * @param UserInterface $user  user instance
	 * @param Input         $input input object
	 *
	 * @return void
	 */
	public function __construct(UserInterface $user, Input $input) {
		// setting user
		$this->_user       = $user;
		$this->_input      = $input;
		$this->_controller = $input->getController();
		$this->_method     = $input->getMethod();
		$this->_package    = Package::getInstance();
		
		// checking permission & validity
		$permission = self::checkPermissions($user, $this->_controller, $this->_method);
		if ($permission === self::PERM_404) {
			$this->_controller = Config::get('environment.error.404.controller');
			$this->_method     = Config::get('environment.error.404.method');
		} else if ($permission === self::PERM_DENIED) {
			$this->_controller = Config::get('environment.error.403.controller');
			$this->_method     = Config::get('environment.error.403.method');
		}
	}

	public static function getLink($href) {
		if (is_array($href)) {
			$href_controller = Config::get('environment.default_controller');
			$href_method     = Config::get('environment.default_method');
			$href_get        = array();
			$href_args       = array();
			extract($href, EXTR_PREFIX_ALL, 'href');

			return $href_controller::getURL($href_method, $href_get, $href_args);
		}
		return $href;
	}

	public static function redirect($url) {
		header('location: '.$url);
		exit();
	}
	
	/**
	 * this function is used to check the permissions of a given controller method combo
	 *
	 * @param UserInterface $user       Instance of user
	 * @param string        $controller Controller Name
	 * @param string        $method     Controller function name
	 *
	 * @return int
	 */
	public static function checkPermissions(UserInterface $user, $controller, $method = null) {
		if ($method === null) {
			$method = Config::get('environment.default_method');
		}
		// validating controller & method
		if (!class_exists($controller, true)
			|| !in_array('Ali\\Base\\ControllerInterface', class_implements($controller))
			|| !method_exists($controller, 'action'.$method)
		) {
			return self::PERM_404;
		}
		
		// getting controller permissions
		$controller_permissions = $controller::getPermissions($method);
		
		// checking permissions
		if (!$user->checkPermissions($controller_permissions)) {
			return self::PERM_DENIED;
		}
		
		// valid permissions
		return self::PERM_GRANTED;
	}
	
	/**
	 * this function prints the full html page for the given contoller and method
	 *
	 * @return void
	 */
	public function html() {
		// getting template
		$controller = $this->_controller;
		$method     = 'action'.$this->_method;
		
		// starting capture of page
		ob_start();
		$app        = new $controller($this->_user, $this->_input);
		$html_title = $app->getTitle($this->_method);
		$app->$method();
		$content = ob_get_clean();

		// getting template information
		$var_template             = $app->getTemplate($this->_method);
		$var_params['controller'] = $app;
		$var_params['title']      = $html_title;
		$var_params['content']    = $content;

		
		// validating template
		if (!class_exists($var_template, true)
			|| !in_array('Ali\\Base\\TemplateInterface', class_implements($var_template))
		) {
			throw new Exception('Unknown Template '.$var_template);
		}
		
		// wrapping content with template header and footer
		$template = new $var_template($this->_user);
		$template->build($var_params);
	}
	
	/**
	 * this function echo's or returns the string of a controller call
	 *
	 * @param boolean $toString true to return string, false to print
	 *
	 * @return string|true
	 */
	public function component($toString = false) {
		// getting app controller & method
		$controller = $this->_controller;
		$method     = 'action'.$this->_method;

		// starting capture of page
		ob_start();
		$app = new $controller($this->_user, $this->_input);
		$app->$method();
		$content = ob_get_clean();
		
		// checking to string
		if ($toString) {
			return $content;
		}
		echo $content;
		return true;
	}
}
