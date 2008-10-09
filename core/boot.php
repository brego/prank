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
	
	private function __construct($start_point) {
		$this->load_base_libs();
		
		c('DS',   DIRECTORY_SEPARATOR);
		c('APP',  dirname(dirname($start_point)).c('DS'));
		c('CORE', dirname(__FILE__).c('DS'));
		
		require_once c('APP').'config'.c('DS').'app.php';
		
		$this->set_error_reporting(c('status'));
		$this->parse_url(isset($_GET['url']) ? $_GET['url'] : null);
		$this->run_controller();
	}
	
	private function load_base_libs() {
		require_once 'config.php';
		require_once 'inflector.php';
		require_once 'base.php';
	}
	
	private function set_error_reporting($status) {
		switch ($status) {
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
	
	public function parse_url($url = null) {
		$path       = array();
		$controller = null;
		$action     = null;
		$params     = array();
		
		// Parsing the URL
		if ($url != null) {
			$url  = explode('/', $url);
			$path = array_cleanup($url);

		// URL parsed, saved to $path
		// Setting & loading the $controller
			if (is_controller($path[0])) {
				$controller = $path[0];
			} elseif (is_controller('default')) {
				$controller = 'default';
			} else {
				$controller = '404';
			}
			load_controller($controller);

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
			load_controller($controller);
		}
		
		self::$path       = $path;
		self::$url        = $url;
		self::$controller = $controller;
		self::$action     = $action;
		self::$params     = $params;
	}
	
	private function run_controller() {
		function partial($name) {
			require_once c('VIEWS').Boot::$controller.c('DS').'_'.$name.'.php';
		}

		try {
			$controller_name   = to_controller(self::$controller);
			$controller_object = new $controller_name;

			$controller_object->set_action(self::$action);
			$controller_object->set_view(self::$action);
			$controller_object->set_params(self::$params);
			$controller_object->set_shortname(self::$controller);

			$controller_object->run();
		} catch (Exception $e) {
			print '<p>Exception: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine().".</p>\n";
			print str_replace("\n", '<br />', $e->getTraceAsString());
		}
	}
	
	public static function run($start_point = null) {
		if (self::$instance === null) {
			self::$instance = new self($start_point);
		}
		return self::$instance;
	}
}
