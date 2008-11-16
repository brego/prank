<?php
/**
 * Booting the framework
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

/**
 * Booting the framework
 *
 * Routing and setup for controllers.
 *
 * @package    Prank
 * @subpackage Core
 */
class Boot {
	private        $url        = null;
	private        $controller = null;
	private        $action     = null;
	private        $params     = array();
 	private        $route      = array();
	private static $instance   = null;

/**
 * Kickstarts the framework
 *
 * @param  string $start_point Full path of index.php (__FILE__)
 * @param  string $config_dir  Full path to the config directory
 * @return void
 */
	public static function run($start_point, $config_dir = false) {
		if (self::$instance === null) {
			self::$instance = new self($start_point, $config_dir);
		}
		return self::$instance;
	}

/**
 * Private constructor
 * 
 * Initializes the framework, and starts the Controller.
 *
 * @param  string $start_point Full path of index.php (__FILE__)
 * @param  string $config_dir  Full path to the config directory
 * @return void
 */
	private function __construct($start_point, $config_dir) {
		
		// session_start();
		$this->load_base_libs();
		
		$registry         = Registry::instance();
		$registry->config = new Config($start_point, $config_dir);
		
		spl_autoload_register('Boot::autoload');
		
		ini_set('include_path', c()->core.c()->ps.c()->app.c()->ps.'.');
		$this->set_error_reporting(c()->state);
		
		$this->url        = isset($_GET['url']) ? $_GET['url'] : '/';
		$registry->router = new Router;
		$this->route      = $registry->router->parse_url($this->url);
		
		$this->parse_route();
		
		$this->run_controller();
	}

/**
 * Autoloader function
 *
 * Registered in the constructor.
 *
 * @param  string $class_name Name of the class to be loaded
 * @return void
 **/
	public static function autoload($class) {
		if (substr($class, -10, 10) !== 'Controller') {

			if (class_exists($class) === false) {
				$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
				$class = str_replace('_', DIRECTORY_SEPARATOR, $class);
				$class = str_replace('prank'.DIRECTORY_SEPARATOR, '', $class);

				if(is_file(c()->core.$class.'.php')) {
					require c()->core.$class.'.php';
				}

				if(is_file(c()->models.$class.'.model.php')) {
					require c()->models.$class.'.model.php';
				}
			}
		}
	}
	
/**
 * Loads Config, Inflector and Base
 *
 * @return void
 */
	private function load_base_libs() {
		require_once 'base.php';
		require_once 'inflector.php';
		require_once 'registry.php';
		require_once 'config.php';
	}

/**
 * Sets error reporting
 *
 * Depending on $state sets proper error_reporting level.
 * 
 * @param  string $state Envoirnmental state
 * @return void
 */
	private function set_error_reporting($state) {
		switch ($state) {
			case 'development':
				error_reporting(E_ALL | E_NOTICE | E_DEPRECATED | E_STRICT);
				break;
			case 'test':
				error_reporting(E_ALL);
				break;
			case 'production':
			default:
				error_reporting(0);
				break;
		}
	}

/**
 * Parses the route, loads controller
 *
 * @return void
 */
	private function parse_route() {
		$controller = null;
		$action     = null;
		$params     = array();
		$route      = $this->route;
		
		if (isset($route['controller']) && is_file(c()->controllers.down($route['controller']).'.controller.php')) {
			$controller = $route['controller'];
			unset($route['controller']);
		} else {
			$controller = 'Http404';
		}
		$this->load_controller($controller);

		if (isset($route['action']) && is_action_of($route['action'], $controller)) {
			$action = $route['action'];
			unset($route['action']);
		} else {
			$action = 'index';
		}

		$params = $route;
		
		$this->controller = $controller;
		$this->action     = $action;
		$this->params     = $params;
	}

/**
 * Runs the Controller determined by Boot::parse_url
 *
 * @return void
 */	
	private function run_controller() {
		try {
			$controller_name   = to_controller($this->controller);
			$controller_object = new $controller_name;

			$controller_object->action     = $this->action;
			$controller_object->view       = $this->action;
			$controller_object->params     = $this->params;
			$controller_object->controller = $this->controller;

			$controller_object->run();
		} catch (Exception $e) {
			echo '<p>Exception: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine().".</p>\n",
				str_replace("\n", "\n<br />", $e->getTraceAsString());
		}
	}
	
/**
 * Loads the controller file
 *
 * @return boolean
 * @param  string  $name Shortname of the controller.
 **/
	private function load_controller($name) {
		if (class_exists(ucfirst($name).'Controller') === false) {
			if (is_file(c()->controllers.down($name).'.controller.php')) {
				require c()->controllers.down($name).'.controller.php';
				return true;
			} elseif (is_file(c()->core.'stubs'.c('ds').'app'.c('ds').'controllers'.c('ds').down($name).'.controller.php')) {
				require c()->core.'stubs'.c('ds').'app'.c('ds').'controllers'.c('ds').down($name).'.controller.php';
				return true;
			} else {
				throw new Exception('File for '.ucfirst($name).'Controller was not found.');
			}
		}
	}

/**
 * For testing purposes only
 * 
 * Returns a property of this object.
 *
 * @param  string $property 
 * @return mixed
 */
	public function test($property) {
		return $this->$property;
	}
}
