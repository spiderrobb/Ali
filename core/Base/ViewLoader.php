<?php
namespace Ali\Base;

use Ali\Config;
use Ali\Package;
use Exception;

trait ViewLoader {
	protected function _view($view, array $data = array(), $toString = false) {
		// setting up include paths
		$paths = Config::get('environment.include_path');

		// building path location for absolute paths
		if ($view[0] === '/') {
			$view = 'View'.$view;
		} else {
			// dynamicly building view path from class name
			$context = debug_backtrace(false)[1];
			if (!isset($context['class'])) {
				var_dump(debug_backtrace(false));
				throw new Exception('Error: must use absolute view path from this location.');
			}
			// building namespace preg_replace
			$patterns = array();
			$replace  = array();
			foreach ($paths as $namespace => $path) {
				$patterns[] = '/^'.str_replace('\\', '\\\\', $namespace).'/';
				$replace[]  = $path.'/View/';
			}
			$view = str_replace('\\', '/', preg_replace($patterns, $replace, $context['class'])).'/'.$view;
		}

		// building file location from view name
		$path = $view.'.php';
		if (file_exists($path)) {
			// including css and javascript
			Package::getInstance()->get($view);
			// including php file of view
			ob_start();
			extract($data);
			include $path;
			$content = ob_get_clean();
		} else {
			$dir = getcwd();
			throw new Exception("View not found ({$path}) in ({$dir})");
		}
		
		// return method
		if ($toString) {
			return $content;
		}
		echo $content;
	}
}
?>