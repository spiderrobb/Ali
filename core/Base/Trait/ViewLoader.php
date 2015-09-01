<?php
namespace Ali\Base\Trait;

use Ali\Config;
use Ali\Package;
use Exception;

trait ViewLoader {
	
	protected function _view($view, array $data = array(), $toString = false) {
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
			$view = 'View/'.str_replace('\\', '/', substr($context['class'], 4)).'/'.$view;
		}
		
		// setting up include paths
		$paths = Config::get('environment.include_path');
		foreach ($paths as $path) {
			// building file location from view name
			$path .= '/'.$view.'.php';
			if (file_exists($path)) {
				// including css and javascript
				Package::getInstance()->get($view);
				// including php file of view
				ob_start();
				extract($data);
				include $path;
				$content = ob_get_clean();
				break;
			}
		}
		
		// checking if view exists
		if (!file_exists($path)) {
			throw new Exception('no view '.$view.'.php, in '.json_encode($paths));
		}
		
		// return method
		if ($toString) {
			return $content;
		}
		echo $content;
	}
}
?>