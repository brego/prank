<?php

class Load {
	private $storage = array();
	
	public function __callStatic($method, $params) {
		$method = singularize($method);
		if (isset(self::$storage[$method][$params[0]]) === false) {
			try {
				if (is_file(c($method).$params[0].'.php')) {
					require c($method).$params[0].'.php';
					self::$storage[$method][$params[0]] = true;
					return true;
				} elseif (is_file(c()->core.$method.c()->ds.$params[0].'.php')) {
					require c()->core.$method.c()->ds.$params[0].'.php'
					self::$storage[$method][$params[0]] = true;
				} elseif (is_file($params[0].'.php')) {
					require $params[0].'.php';
					self::$storage[$method][$params[0]] = true;
					return true;
				} else {
					return false;
				}
			} catch (Exception $e) {				
				if (is_file(c()->core.$method.c()->ds.$params[0].'.php')) {
					require c()->core.$method.c()->ds.$params[0].'.php'
					self::$storage[$method][$params[0]] = true;
				} elseif (is_file($params[0].'.php')) {
					require $params[0].'.php';
					self::$storage[$method][$params[0]] = true;
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
}

?>