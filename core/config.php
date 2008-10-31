<?php
/**
 * Config
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
 * Core config class (a Singelton)
 *
 * @package    Prank
 * @subpackage Core
 */
class Config {
	private static $instance = null;
	private static $config   = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
/**
 * Returns the instance
 *
 * @return Config
 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

/**
 * Setups the configuration for Prank
 *
 * $start_point needs to be the an output of the __FILE__ from the starting
 * point of the application. It will be used to determine full paths for
 * directories.
 * 
 * @param  string $start_point 
 * @return void
 */
	public static function setup($start_point) {
		$config        = new stdClass;
		$config->ds    = DIRECTORY_SEPARATOR;
		$config->ps    = PATH_SEPARATOR;
		$config->app   = dirname(dirname($start_point)).$config->ds;
		$config->core  = dirname(__FILE__).$config->ds;
		$config->prank = dirname(dirname(__FILE__)).$config->ds;
		$config->lib   = $config->prank.'lib'.$config->ds;
		
		$default_app_config = array(
			'state'       => 'development',
			'directories' => array(
				'models'      => 'models',
				'views'       => 'views',
				'controllers' => 'controllers',
				'webroot'     => 'webroot'));
		
		$app_config = array();
		if (is_file($config->app.'config'.$config->ds.'app.yml')) {
			$app_config = from_yaml_file($config->app.'config'.$config->ds.'app.yml');
		}
		$app_config = array_merge($default_app_config, $app_config);
		
		$config->state       = $app_config['state'];
		$config->models      = $config->app.$app_config['directories']['models'].$config->ds;
		$config->views       = $config->app.$app_config['directories']['views'].$config->ds;
		$config->controllers = $config->app.$app_config['directories']['controllers'].$config->ds;
		$config->webroot     = $config->app.$app_config['directories']['webroot'].$config->ds;
		
		if (is_file($config->app.'config'.$config->ds.'db.yml')) {
			$db_config = from_yaml_file($config->app.'config'.$config->ds.'db.yml');
		} else {
			throw new Exception('Currently Prank requires a database connection. Provide a config/db.yml with necessary data.');
		}
		
		$config->db = new stdClass;
		foreach ($db_config[$config->state] as $key => $value) {
			$config->db->$key = $value;
		}
		
		self::$config = $config;
	}

/**
 * Sets a configuration variable
 *
 * @param  string $name 
 * @param  mixed  $value 
 * @return void
 */
	public static function set($name, $value) {
		self::$config->$name = $value;
	}

/**
 * Overload for setting of a configuration variable
 *
 * Uses Config::set().
 * 
 * @param  string $name 
 * @param  mixed  $value 
 * @return void
 */
	public function __set($name, $value) {
		self::set($name, $value);
	}
	
/**
 * Returns the value of the given configuration variable, or the whole object
 *
 * If given a name, returns the value. If not, returns the whole configuration
 * object. If the requested $name is not set, Exception will be thrown.
 * 
 * @param  mixed $name 
 * @return mixed
 */
	public static function get($name = false) {
		if ($name === false) {
			return self::$config;
		} else {
			if (isset(self::$config->$name)) {
				return self::$config->$name;
			} else {
				throw new Exception('Property '.$name.' is not defined in the configuration.');
			}
		}
	}

/**
 * Overload for getting the configuration variable
 * 
 * Uses Config::get().
 *
 * @param  string $name 
 * @return mixed
 */
	public function __get($name) {
		return self::get($name);
	}
}

?>