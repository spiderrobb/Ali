<?php
namespace Ali\Base;

use Ali\Config;
use Ali\Package;
use Exception;

trait ViewLoader {
	private $_last_context = false;
	protected function _view($view, array $data = array(), $toString = false) {
		// getting last context
		$prev_context = $this->_last_context;
		$context = debug_backtrace(false)[1];
		if (isset($context['class'])) {
			$this->_last_context = $context;
		}

		// building path location for absolute paths
		if ($view[0] === '/') {
			// do nothing
			$view = ltrim($view, '/');
		} else {
			// setting up include paths
			$paths = Config::get('environment.include_path');
			// building namespace preg_replace
			$patterns = array();
			$replace  = array();
			foreach ($paths as $namespace => $path) {
				$patterns[] = '/^'.str_replace('\\', '\\\\', $namespace).'/';
				$replace[]  = $path.'/View/';
			}
			$view = str_replace('\\', '/', preg_replace($patterns, $replace, $this->_last_context['class'])).'/'.$view;
		}

		// building file location from view name
		$path = $view.'.php';
		if (file_exists($path)) {
			// including php file of view
			ob_start();
			extract($data);
			include $path;
			$content = ob_get_clean();
			// including css and javascript do this after the view so dependencies work
			Package::getInstance()->get($view);
		} else {
			$dir = getcwd();
			throw new Exception("View not found ({$path}) in ({$dir})");
		}

		// returning last context to prior state
		$this->_last_context = $prev_context;
		
		// return method
		if ($toString) {
			return $content;
		}
		echo $content;
	}
}
?>