<?php
/**
 * Booting the framework
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
 * Booting the framework
 *
 * Routing and setup for controllers.
 *
 * @package    Prank
 * @subpackage Core
 */
class Boot {
	private $url        = null;
	private $controller = null;
	private $action     = null;
	private $params     = [];
	private $route      = [];
	private $config     = null;

/**
 * Constructor
 *
 * Initializes the framework, and starts the Controller.
 *
 * @param  string $start_point Full path of index.php (__FILE__)
 * @param  string $config_dir  Full path to the config directory
 * @return void
 */
	public function __construct($start_point, $config_dir = false) {
		ob_start();

		require 'base.php';
		require 'registry.php';
		require 'config.php';

		$registry         = Registry::instance();
		$config           = new Config($start_point, $config_dir);
		$this->config     = $config;
		$registry->config = $config;

		use_helper('inflector.php', 'base.php');

		spl_autoload_register('Boot::autoload');

		ini_set('include_path', $config['core'].$config['ps'].$config['app'].$config['ps'].'.');
		$this->set_error_reporting($config['state']);

		$this->url        = isset($_GET['url']) ? $_GET['url'] : '/';
		$registry->router = new Router($config);
		$this->route      = $registry->router->parse_url($this->url);

		$this->parse_route();

		$this->run_controller();

		ob_end_flush();
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
				$class_underscore = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
				$class = str_replace('_', DIRECTORY_SEPARATOR, $class_underscore);

				$registry = Registry::instance();

				if (is_file($registry->config['core'].$class.'.php')) {
					require $registry->config['core'].$class.'.php';
				}

				if (is_file($registry->config['models'].$class_underscore.'.model.php')) {
					require $registry->config['models'].$class_underscore.'.model.php';
				}
			}
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
		$params     = [];
		$route      = $this->route;

		if (isset($route['controller']) && is_file($this->config['controllers'].down($route['controller']).'.controller.php')) {
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
			$registry          = Registry::instance();
			$controller_name   = to_controller($this->controller);
			$controller_object = new $controller_name($this->action, $this->action, $this->params, $this->controller, $this->config);

			$registry->current_controller = $controller_object;

			$controller_object->run($this->action, $this->action, $this->params, $this->controller, $this->config);

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
			if (is_file($this->config['controllers'].down($name).'.controller.php')) {
				require $this->config['controllers'].down($name).'.controller.php';
				return true;
			} elseif (is_file(file_path($this->config['core'].'stubs', 'app', 'controllers', down($name).'.controller.php'))) {
				require file_path($this->config['core'].'stubs', 'app', 'controllers', down($name).'.controller.php');
				return true;
			} else {
				throw new Exception('File for '.ucfirst($name).'Controller was not found.');
			}
		}
	}

/**
 * Backwards compatible instantiation of the Boot class.
 *
 * @param  string $start_point
 * @param  string $config_dir
 * @return void
 */
	public static function run($start_point, $config_dir = false) {
		return new self($start_point, $config_dir);
	}
}

?>