<?php
/**
 * Config
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @link       http://cakephp.org CakePHP's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

class Config {
	private static $instance = null;
	private static $config   = array();
	
	private function __construct() {}
	
	private function __clone() {}
	
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
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
	
	public static function set($name, $value) {
		self::$config->$name = $value;
	}
	
	public function __set($name, $value) {
		self::set($name, $value);
	}
	
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
	
	public function __get($name) {
		return self::get($name);
	}
}
