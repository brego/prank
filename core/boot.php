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
	private static $instance   = null;
	public  static $url        = null;
	public  static $controller = null;
	public  static $action     = null;
	public  static $params     = array();
 	public  static $route      = array();

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
		
		session_start();
		
		$this->load_base_libs();
		
		Config::setup($start_point, $config_dir);
		
		ini_set('include_path', c()->core.c()->ps.c()->app.c()->ps.'.');
		$this->set_error_reporting(c()->state);
		
		self::$url = isset($_GET['url']) ? $_GET['url'] : '/';
		
		$router = new Router;
		self::$route = $router->parse_url(self::$url);
		
		$this->parse_route();
		
		$this->run_controller();
	}
	
/**
 * Loads Config, Inflector and Base
 *
 * @return void
 */
	private function load_base_libs() {
		if (class_exists('Config') === false) {
			require 'config.php';
		}
		if (class_exists('Inflector') === false) {
			require 'inflector.php';
		}
		if (function_exists('__autoload') === false) {
			require 'base.php';
		}
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
		$route      = self::$route;
		
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
		
		self::$controller = $controller;
		self::$action     = $action;
		self::$params     = $params;
	}

/**
 * Runs the Controller determined by Boot::parse_url
 *
 * @return void
 */	
	private function run_controller() {
		try {
			$controller_name   = Inflector::to_controller(self::$controller);
			$controller_object = new $controller_name;

			$controller_object->action     = self::$action;
			$controller_object->view       = self::$action;
			$controller_object->params     = self::$params;
			$controller_object->controller = self::$controller;

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
}
