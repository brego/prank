<?php

class Router {
	private static $current_route = null;
	private        $routes        = array();
	
	public function __construct() {
		$this->register_routes();
	}
	
	public function routes() {
		return $this->routes;
	}

	public static function current_route() {
		return self::$current_route;
	}
	
	public function parse_url($url) {
		$out = array();
		$ext = null;

		if (strpos($url, '/') !== 0) {
			$url = '/' . $url;
		}
		
		foreach ($this->routes as $route => $params) {
			if (($matches = $this->match_route($route, $url)) !== false) {
				$names    = $params['names'];
				$defaults = $params['defaults'];
				
				array_shift($matches);

				foreach ($matches as $key => $found) {
					if (empty($found)) {
						continue;
					}
					if (isset($names[$key])) {
						$out[$names[$key]] = $found;
					} else {
						foreach (array_cleanup(split('/', $found)) as $param) {
							array_push($out, $param);
						}
					}
				}
				
				$out = array_merge($defaults, $out);
				
				break;
			}
		}
		
		self::$current_route = $out;
		
		return $out;
	}
	
	private function register_routes() {
		$map = $this;
		require c()->config.'routes.php';
	}
	
	private function connect($route, $params = array()) {
		list($expression, $names) = $this->parse_route($route);
		$this->routes[$expression] = array('names'=>$names, 'defaults'=>$params);
	}
	
	public function __call($method, $arguments) {
		throw new Exception('Nope');
	}
	
	private function parse_route($route) {
		if (empty($route) || $route === '/') {
			return array('/^[\/]*$/', array());
		}
		$names    = array();
		$parsed   = array();
		$elements = explode('/', $route);

		foreach ($elements as $element) {
			if (empty($element) === true) {
				continue;
			}
			$element     = trim($element);
			$named_param = strpos($element, ':') !== false;

			if ($named_param === true && preg_match('/^:([^:]+)$/', $element, $name) !== 0) {
				$parsed[] = '(?:/([^\/]+))?';
				$names[]  = $name[1];
			} elseif ($named_param && preg_match_all('/(?!\\\\):([a-z_0-9]+)/i', $element, $matches)) {
				$matchCount = count($matches[1]);

				foreach ($matches[1] as $i => $name) {
					$pos     = strpos($element, ':' . $name);
					$before  = substr($element, 0, $pos);
					$element = substr($element, $pos+strlen($name)+1);
					$after   = null;
					if ($i + 1 == $matchCount && $element) {
						$after = preg_quote($element);
					}

					if ($i == 0) {
						$before = '/' . $before;
					}

					$before   = preg_quote($before, '#');
					$parsed[] = '(?:' . $before . '([^\/]+)' . $after . ')?';
					$names[]  = $name;
				}
			} else {
				$parsed[] = '/' . $element;
			}
		}
		return array('#^' . join('', $parsed) . '[\/]*(?:([\/\-A-Za-z_0-9]+))?$#', $names);
	}
	
	private function match_route($route, $url) {
		$match = preg_match($route, $url, $matches);
		if ($match === 0) {
			return false;
		} else {
			return $matches;
		}
	}
}

?>