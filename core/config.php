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
 * @version    Prank 0.25
 */

/**
 * Core config class
 *
 * @package    Prank
 * @subpackage Core
 */
class Config {
	private $config = null;

/**
 * Constructor
 * 
 * If $start_point is set, calls automatically on Config::setup.
 *
 * @param  string $start_point 
 * @param  string $config_dir 
 * @return void
 */
	public function __construct($start_point = false, $config_dir = false) {
		if ($start_point !== false) {
			$this->setup($start_point, $config_dir);
		}
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
	public function setup($start_point, $config_dir = false) {
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
				'webroot'     => 'webroot',
				'config'      => 'config',
				'helpers'     => 'helpers'));
				
		if ($config_dir === false) {
			$config_dir = $config->app.'config'.$config->ds;
		}
		
		$app_config = array();
		if (is_file($config_dir.'app.yml')) {
			$app_config = from_yaml_file($config_dir.'app.yml');
		}
		$app_config = array_merge($default_app_config, $app_config);
		
		$config->state       = $app_config['state'];
		$config->models      = $config->app.$app_config['directories']['models'].$config->ds;
		$config->views       = $config->app.$app_config['directories']['views'].$config->ds;
		$config->controllers = $config->app.$app_config['directories']['controllers'].$config->ds;
		$config->webroot     = $config->app.$app_config['directories']['webroot'].$config->ds;
		$config->config      = $config->app.$app_config['directories']['config'].$config->ds;
		$config->helpers     = $config->app.$app_config['directories']['helpers'].$config->ds;
		
		if (is_file($config->config.'db.yml')) {
			$db_config = from_yaml_file($config->app.'config'.$config->ds.'db.yml');
		} else {
			throw new Exception('Currently Prank requires a database connection. Provide a config/db.yml with necessary data.');
		}
		$config->db = new stdClass;
		foreach ($db_config[$config->state] as $key => $value) {
			$config->db->$key = $value;
		}
		
		$this->config = $config;
	}

/**
 * Overload for setting of a configuration variable
 * 
 * @param  string $name 
 * @param  mixed  $value 
 * @return void
 */
	public function __set($name, $value) {
		$this->config->$name = $value;
	}

/**
 * Overload for getting the configuration variable
 *
 * @param  string $name 
 * @return mixed
 */
	public function __get($name) {
		if (isset($this->config->$name)) {
			return $this->config->$name;
		} else {
			throw new Exception('Property '.$name.' is not defined in the configuration.');
		}
	}
}

?>