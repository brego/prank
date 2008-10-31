<?php
/**
 * Booting the framework.
 *
 * Routing and setup for controllers.
 *
 * PHP version 5.3.
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

class Boot {
	private static $instance = null;
	
	public  static $path       = array();
	public  static $url        = null;
	public  static $controller = null;
	public  static $action     = null;
	public  static $params     = array();
	private        $config     = null;

/**
 * Kickstarts the framework
 *
 * @param  string $start_point Full path of index.php (__FILE__)
 * @return void
 */
	public static function run($start_point = null) {
		if (self::$instance === null) {
			self::$instance = new self($start_point);
		}
		return self::$instance;
	}

/**
 * Private constructor
 * 
 * Initializes the framework, and starts the Controller.
 *
 * @param  string $start_point Full path of index.php (__FILE__)
 * @return void
 */
	private function __construct($start_point) {
		$this->load_base_libs();
		
		Config::setup($start_point);
		
		ini_set('include_path', c()->core.c()->ps.c()->app.c()->ps.'.');
		
		$this->set_error_reporting(c()->state);
		$this->parse_url(isset($_GET['url']) ? $_GET['url'] : null);
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
 * Parse given url
 * 
 * Parses given url, and adapts it's elements as basic values for booting.
 *
 * @param  string $url 
 * @return void
 */
	public function parse_url($url = null) {
		$path       = array();
		$controller = null;
		$action     = null;
		$params     = array();
		
		// Parsing the URL
		if ($url != null) {
			$url  = split('/', $url);
			$path = array_cleanup($url);

		// URL parsed, saved to $path
		// Setting & loading the $controller
			if ($this->is_controller($path[0])) {
				$controller = $path[0];
			} elseif ($this->is_controller('default')) {
				$controller = 'default';
			} else {
				$controller = '404';
			}
			$this->load_controller($controller);

		// Setting the $action
			if ($controller == $path[0] && count($path) > 1 && is_action_of($path[1], $controller)) {
				$action = $path[1];
			} elseif (is_action_of($path[0], $controller)) {
				$action = $path[0];
			} else { //if (is_action_of('index', $controller)) {
				$action = 'index';
			}

		// Setting the $params
			if ($controller == $path[0] && count($path) > 1 && $action == $path[1]) {
				unset($path[0], $path[1]);
				$params = $path;
			} elseif ($action == $path[0]) {
				unset($path[0]);
				$params = $path;
			} else {
				$params = $path;
			}
		} else {
			$controller = 'default';
			$action     = 'index';
			$this->load_controller($controller);
		}
		
		self::$path       = $path;
		self::$url        = $url;
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
		function partial($name) {
			require c()->views.Boot::$controller.c()->ds.'_'.$name.'.php';
		}

		try {
			$controller_name   = Inflector::to_controller(self::$controller);
			$controller_object = new $controller_name;

			$controller_object->action    = self::$action;
			$controller_object->view      = self::$action;
			$controller_object->params    = self::$params;
			$controller_object->shortname = self::$controller;

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
			if ($this->is_controller($name)) {
				require c()->controllers.down($name).'.controller.php';
				return true;
			} else {
				return false;
			}
		}
	}
	
/**
 * Checks if controller file exists
 *
 * Checks if the $name exists in the controllers directory.
 *
 * @return boolean
 * @param  string  $name Shortname of the controller
 **/
	private function is_controller($name) {
		if (file_exists(c()->controllers.down($name).'.controller.php')) {
			return true;
		} else {
			return false;
		}
	}
}
