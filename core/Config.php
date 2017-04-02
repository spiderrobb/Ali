<?php
namespace Ali;
class Config {
	private static $_config_path = null;
	public static function setConfigPath($path) {
		self::$_config_path = $path;
	}
	public static function get($param, $default = null) {
		// init static data to cache results
		static $data = array();

		// split param into parts
		$path = explode('.', $param);

		// checking if config file has been loaded yet
		if (!isset($data[$path[0]])) {
			// checking for config file
			$file_path = self::$_config_path.$path[0].'.json';
			if (file_exists($file_path)) {
				// loading and parsing config file
				$data[$path[0]] = json_decode(file_get_contents($file_path), true);
			}

			// checking if we are on development environment
			// if (isset($_SERVER['APPLICATION_ID']) && strpos($_SERVER['APPLICATION_ID'], 'dev~') !== false) {
			// 	// checking for config file
			// 	$file_path = __DIR__.'/../config-dev/'.$path[0].'.json';
			// 	if (file_exists($file_path)) {
			// 		// loading and parsing config file
			// 		$dev_data = json_decode(file_get_contents($file_path), true);
			// 		// checking if there is a file to overwrite
			// 		if (!is_array($dev_data)
			// 			|| !isset($data[$path[0]]) 
			// 			|| !is_array($data[$path[0]])
			// 		) {
			// 			$data[$path[0]] = $dev_data;
			// 		} else {
			// 			$data[$path[0]] = self::mergeArray($data[$path[0]], $dev_data);
			// 		}
			// 	}
			// }
		}

		// walking through data on path
		$mydata = $data;
		foreach ($path as $dir) {
			if (!isset($mydata[$dir])) {
				return $default;
			}
			$mydata = $mydata[$dir];
		}

		// return end point in data
		return $mydata;
	}
	// public static function mergeArray($config, $new_config) {
	// 	foreach ($new_config as $key => $value) {
	// 		if (!is_array($value)
	// 			|| !isset($config[$key]) 
	// 			|| !is_array($config[$key])
	// 		) {
	// 			$config[$key] = $value;
	// 		} else {
	// 			$config[$key] = self::mergeArray($config[$key], $value);
	// 		}
	// 	}
	// 	return $config;
	// }
}
