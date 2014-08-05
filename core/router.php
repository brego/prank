<?php
/**
 * Routing the url
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Routing the url
 *
 * Parses given url acording to registered connections.
 *
 * @package    Prank
 * @subpackage Core
 */
class Router {
	private static $current_route = null;
	private        $routes        = [];
	private        $config        = null;

/**
 * Registers routes
 *
 * Calls Router::register_routes().
 *
 * @return void
 */
	public function __construct($config) {
		$this->config = $config;
		$this->register_routes();
	}

/**
 * Returns current routes
 *
 * @return array
 */
	public function routes() {
		return $this->routes;
	}

/**
 * Returns the current route
 *
 * @return array
 */
	public static function current_route() {
		return self::$current_route;
	}

/**
 * Parses givven url, tries to match it against routes
 *
 * @param  string $url
 * @return array  Current route
 */
	public function parse_url($url) {
		$out = [];
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
					if (empty($found) && $found !== '0' && $found !== 0) {
						continue;
					}
					if (isset($names[$key])) {
						$out[$names[$key]] = $found;
					} else {
						foreach (array_cleanup(explode('/', $found)) as $param) {
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

/**
 * Loads user-defined routes
 *
 * Loads app/config/routes.php. Defines a local variable $map, referencing
 * $this.
 *
 * @return void
 */
	private function register_routes() {
		$map = $this;
		require $this->config['config'].'routes.php';
	}

/**
 * Connects given route
 *
 * Prank uses Rails'esque keywords preceeded with a colon for $route.
 * :controller and :action are speciall - as they decide which controller and
 * action will be run by Prank. You can provide default values for keywords.
 * Keep in mind that Prank supports unnamed attributes (they will not be
 * directly passed to the action though).
 *
 * Uses Router::parse_route() for logic.
 *
 * @todo   Implement ressourcess
 * @param  string $route
 * @param  array  $defaults
 * @return void
 */
	private function connect($route, $defaults = array()) {
		list($expression, $names) = $this->parse_route($route);
		$this->routes[$expression] = ['names' => $names, 'defaults' => $defaults];
	}
	
/**
 * This will make support for named routes possible
 *
 * @todo   Implement named routes
 * @param  string $method
 * @param  mixed  $arguments
 * @return void
 */
	public function __call($method, $arguments) {
		throw new Exception('Nope');
	}

/**
 * Parses a user-defined route
 *
 * Outputs an array consisting of a regular expression, and an array of names
 * (keyowrds) found.
 *
 * @param  string $route
 * @return array
 */
	private function parse_route($route) {
		if (empty($route) || $route === '/') {
			return array('/^[\/]*$/', array());
		}
		$names    = [];
		$parsed   = [];
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

/**
 * Checks if $route matches the $url
 *
 * If a match is found, all the submatches of the regex are returned.
 *
 * @param  string $route
 * @param  string $url
 * @return mixed  False or array of submatches
 */
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