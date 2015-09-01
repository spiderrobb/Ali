<?php
namespace Ali;

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
	 * @param User  $user  user instance
	 * @param Input $input input object
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
			$this->_controller = Config::get('environment.error.permission.controller');
			$this->_method     = Config::get('environment.error.permission.method');
		}
	}

	public static function redirect($url) {
		header('location: '.$url);
		exit();
	}
	
	/**
	 * this function is used to check the permissions of a given controller method combo
	 *
	 * @param User   $user       Instance of user
	 * @param string $controller Controller Name
	 * @param string $method     Controller function name
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
		$var_template               = $app->getTemplate($this->_method);
		$var_params['html_title']   = $html_title;
		$var_params['html_content'] = $content;

		
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
	
	/**
	 * this function prints a json encoded array with 4 attributes:
	 * 'scripts', 'styles', 'calls' and 'html'
	 * the page is rendered without the template html
	 * the scripts attribute is an array of all javascript files needed for the html
	 * the styles  attribute is an array of all stylesheet files needed for the html
	 * the calls   attribute is a chunk of javascript that needs to be executed after load
	 * the html    attribute is the html associated with the request
	 *
	 * @return void
	 */
	public function json() {
		// getting controller and method
		$controller = $this->_controller;
		$method     = 'action'.$this->_method;
		
		// getting html
		ob_start();
		//$this->_package->generateCustomStyle();
		$app = new $controller($this->_user, $this->_input);
		$app->$method();
		$content = ob_get_clean();
		
		// building json array
		echo json_encode(array(
			'scripts' => $this->_package->getScriptLinks(),
			'styles'  => $this->_package->getStyleLinks(),
			'calls'   => $this->_package->getScriptCalls(),
			'html'    => $content
		));
	}
}
?>